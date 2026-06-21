<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Subscribers\ExportSubscribersAction;
use App\Actions\Subscribers\ImportSubscribersAction;
use App\Actions\Subscribers\SendConfirmationMailAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Subscribers\ImportSubscribersRequest;
use App\Http\Requests\Admin\Subscribers\StoreSubscriberRequest;
use App\Http\Requests\Admin\Subscribers\UpdateSubscriberRequest;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Subscriber::class);

        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['email', 'name', 'created_at', 'confirmed_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $query = Subscriber::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        match ($status) {
            'pending' => $query->pending(),
            'active' => $query->active(),
            'unsubscribed' => $query->unsubscribed(),
            default => null,
        };

        $subscribers = $query->orderBy($sort, $direction)
            ->paginate(25)
            ->withQueryString();

        $counts = [
            'all' => Subscriber::count(),
            'pending' => Subscriber::pending()->count(),
            'active' => Subscriber::active()->count(),
            'unsubscribed' => Subscriber::unsubscribed()->count(),
        ];

        return view('admin.subscribers.index', compact(
            'subscribers',
            'status',
            'search',
            'sort',
            'direction',
            'counts',
        ));
    }

    public function create()
    {
        $this->authorize('create', Subscriber::class);

        return view('admin.subscribers.create');
    }

    public function store(StoreSubscriberRequest $request, SendConfirmationMailAction $action)
    {
        $subscriber = DB::transaction(function () use ($request) {
            return Subscriber::create($request->validated());
        });

        $sent = $action->execute($subscriber);

        return redirect()
            ->route('admin.subscribers.index')
            ->with('success', $sent
                ? __('Abonnee toegevoegd. Bevestigingsmail is verzonden.')
                : __('Abonnee toegevoegd.'));
    }

    public function edit(Subscriber $subscriber)
    {
        $this->authorize('update', $subscriber);

        return view('admin.subscribers.edit', compact('subscriber'));
    }

    public function update(UpdateSubscriberRequest $request, Subscriber $subscriber)
    {
        $subscriber->update($request->validated());

        return redirect()
            ->route('admin.subscribers.index')
            ->with('success', __('Abonnee bijgewerkt.'));
    }

    public function destroy(Subscriber $subscriber)
    {
        $this->authorize('delete', $subscriber);

        $subscriber->delete();

        return redirect()
            ->route('admin.subscribers.index')
            ->with('success', __('Abonnee verwijderd.'));
    }

    public function sendConfirmation(Subscriber $subscriber, SendConfirmationMailAction $action)
    {
        $this->authorize('sendConfirmation', $subscriber);

        $sent = $action->execute($subscriber);

        return redirect()
            ->route('admin.subscribers.index', request()->only(['status', 'search', 'sort', 'direction', 'page']))
            ->with($sent ? 'success' : 'warning', $sent
                ? __('Bevestigingsmail verzonden naar :email.', ['email' => $subscriber->email])
                : __('Geen mail verzonden — abonnee is al bevestigd of uitgeschreven.'));
    }

    public function sendBulkConfirmations(SendConfirmationMailAction $action)
    {
        $this->authorize('create', Subscriber::class);

        $sent = 0;
        $skipped = 0;

        Subscriber::pending()->chunkById(50, function ($pendings) use ($action, &$sent, &$skipped) {
            foreach ($pendings as $subscriber) {
                $action->execute($subscriber) ? $sent++ : $skipped++;
            }
        });

        return redirect()
            ->route('admin.subscribers.index')
            ->with('success', __('Bevestigingsmail verstuurd naar :sent pending abonnees.', ['sent' => $sent]));
    }

    public function import(ImportSubscribersRequest $request, ImportSubscribersAction $action)
    {
        $result = $action->execute($request->file('file'));

        $messageParts = [];
        if ($result->created > 0) {
            $messageParts[] = __(':n nieuw', ['n' => $result->created]);
        }
        if ($result->existing > 0) {
            $messageParts[] = __(':n al bekend', ['n' => $result->existing]);
        }
        if ($result->unsubscribed > 0) {
            $messageParts[] = __(':n eerder uitgeschreven', ['n' => $result->unsubscribed]);
        }
        if ($result->hasErrors()) {
            $messageParts[] = __(':n ongeldig', ['n' => count($result->errors)]);
        }

        $message = __('Import voltooid:').' '.implode(' · ', $messageParts).'.';

        $redirect = redirect()->route('admin.subscribers.index')
            ->with($result->hasErrors() ? 'warning' : 'success', $message);

        if ($result->hasErrors()) {
            $redirect
                ->with('flash_action_url', route('admin.subscribers.import-errors', $result->errorReportToken))
                ->with('flash_action_label', __('Download foutrapport (:n regels)', ['n' => count($result->errors)]));
        }

        return $redirect;
    }

    public function downloadErrorReport(string $token)
    {
        $this->authorize('import', Subscriber::class);

        $path = ImportSubscribersAction::errorReportPath($token);
        $disk = Storage::disk(ImportSubscribersAction::errorReportDisk());

        abort_unless($disk->exists($path), 404);

        return response()->download(
            $disk->path($path),
            'import-fouten.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public function importTemplate(): StreamedResponse
    {
        $this->authorize('import', Subscriber::class);

        return response()->streamDownload(function () {
            echo "email,name\n";
            echo "voorbeeld@example.com,Voorbeeld Naam\n";
        }, 'abonnees-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function export(Request $request, ExportSubscribersAction $action): StreamedResponse
    {
        $this->authorize('export', Subscriber::class);

        $filters = $request->only(['status', 'search']);
        $csv = $action->execute($filters);

        $filename = 'abonnees-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(
            function () use ($csv) {
                echo $csv;
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ],
        );
    }
}
