<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Newsletters\SendTestNewsletterRequest;
use App\Http\Requests\Admin\Newsletters\StoreNewsletterRequest;
use App\Http\Requests\Admin\Newsletters\UpdateNewsletterRequest;
use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Mews\Purifier\Facades\Purifier;

class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Newsletter::class);

        $query = Newsletter::query()->with('author');

        // Zoeken op subject
        if ($search = $request->string('search')->trim()->value()) {
            $query->where('subject', 'like', "%{$search}%");
        }

        // Statusfilter: draft / sending / sent / all
        $status = $request->string('status', 'all')->value();
        if (! in_array($status, ['all', 'draft', 'sending', 'sent'], true)) {
            $status = 'all';
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Sortering
        $sort = $request->string('sort', 'created_at')->value();
        $direction = $request->string('direction', 'desc')->value() === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, ['subject', 'status', 'sent_at', 'created_at'], true)) {
            $sort = 'created_at';
        }

        $newsletters = $query->orderBy($sort, $direction)
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all' => Newsletter::query()->count(),
            'draft' => Newsletter::draft()->count(),
            'sending' => Newsletter::sending()->count(),
            'sent' => Newsletter::sent()->count(),
        ];

        return view('admin.newsletters.index', compact(
            'newsletters',
            'counts',
            'search',
            'status',
            'sort',
            'direction'
        ));
    }

    public function create()
    {
        $this->authorize('create', Newsletter::class);

        return view('admin.newsletters.create');
    }

    public function store(StoreNewsletterRequest $request)
    {
        // Authorization via StoreNewsletterRequest::authorize()
        $data = $request->validated();

        // Niet-kolommen apart afhandelen
        $headerFile = $request->file('header');
        $data = Arr::except($data, ['header']);

        // Body saneren via mews/purifier 'simple'-profiel (consistent met Pages)
        $data['body'] = Purifier::clean($data['body'], 'simple');

        // Auteur = ingelogde gebruiker
        $data['user_id'] = $request->user()->id;

        $newsletter = Newsletter::create($data);

        // Header-image (single) — meegestuurd vanaf het create-formulier
        if ($headerFile) {
            $newsletter->addMediaFromRequest('header')->toMediaCollection('header');
        }

        return redirect()
            ->route('admin.newsletters.edit', $newsletter)
            ->with('success', __('Concept aangemaakt.'));
    }

    public function edit(Newsletter $newsletter)
    {
        $this->authorize('update', $newsletter);

        $newsletter->load('media');

        return view('admin.newsletters.edit', compact('newsletter'));
    }

    public function update(UpdateNewsletterRequest $request, Newsletter $newsletter)
    {
        // Authorization via UpdateNewsletterRequest::authorize() → NewsletterPolicy
        $data = $request->validated();

        $hasHeaderFile = $request->hasFile('header');
        $removeHeader = $request->boolean('remove_header');
        $data = Arr::except($data, ['header', 'remove_header']);

        $data['body'] = Purifier::clean($data['body'], 'simple');

        $newsletter->update($data);

        if ($hasHeaderFile) {
            $newsletter->clearMediaCollection('header');
            $newsletter->addMediaFromRequest('header')->toMediaCollection('header');
        } elseif ($removeHeader) {
            $newsletter->clearMediaCollection('header');
        }

        return redirect()
            ->route('admin.newsletters.index')
            ->with('success', __('Nieuwsbrief bijgewerkt.'));
    }

    public function destroy(Newsletter $newsletter)
    {
        $this->authorize('delete', $newsletter);

        // Hard delete — Newsletter is geen core content, geen trash-module-deelnemer
        $newsletter->delete();

        return redirect()
            ->route('admin.newsletters.index')
            ->with('success', __('Nieuwsbrief verwijderd.'));
    }

    public function sendTest(SendTestNewsletterRequest $request, Newsletter $newsletter)
    {
        // Autorisatie via SendTestNewsletterRequest::authorize() -> NewsletterPolicy::sendTest()
        // Beslissing #48: testmail naar auth()->user()->email, [TEST]-prefix in subject,
        // geen NewsletterSend-row aangemaakt. Niet-klikbare unsubscribe-token houdt de
        // testmail visueel identiek aan een echte mail zonder abuse-vector.
        $placeholderUnsubscribeUrl = url('/nieuwsbrief/uitschrijven/'.str_repeat('0', 64));

        Mail::to($request->user()->email)
            ->send(new NewsletterMail(
                newsletter: $newsletter,
                unsubscribeUrl: $placeholderUnsubscribeUrl,
                isTest: true,
            ));

        return redirect()
            ->route('admin.newsletters.edit', $newsletter)
            ->with('success', __('Testmail verzonden naar :email.', ['email' => $request->user()->email]));
    }
}
