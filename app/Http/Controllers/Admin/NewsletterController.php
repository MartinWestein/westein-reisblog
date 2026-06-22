<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use Illuminate\Http\Request;

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
}
