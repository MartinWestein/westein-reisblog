<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Pages\StorePageRequest;
use App\Http\Requests\Admin\Pages\UpdatePageRequest;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Page::class);

        $query = Page::query();

        // Zoeken op titel of slug
        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Statusfilter: draft / scheduled / published / all
        $status = $request->string('status', 'all')->value();
        if (! in_array($status, ['all', 'draft', 'scheduled', 'published'], true)) {
            $status = 'all';
        }

        match ($status) {
            'draft' => $query->whereNull('published_at'),
            'scheduled' => $query->whereNotNull('published_at')->where('published_at', '>', now()),
            'published' => $query->whereNotNull('published_at')->where('published_at', '<=', now()),
            default => null,
        };

        // Sortering
        $sort = $request->string('sort', 'order')->value();
        $direction = $request->string('direction', 'asc')->value() === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, ['title', 'order', 'published_at', 'created_at'], true)) {
            $sort = 'order';
        }

        $pages = $query->orderBy($sort, $direction)
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pages.index', compact('pages', 'sort', 'direction', 'status'));
    }

    public function create()
    {
        $this->authorize('create', Page::class);

        return view('admin.pages.create');
    }

    public function store(StorePageRequest $request)
    {
        // Authorization gebeurt al in StorePageRequest::authorize()
        $data = $request->validated();

        // Slug auto-genereren als leeg gelaten
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Body saneren via mews/purifier 'simple'-profiel
        $data['body'] = Purifier::clean($data['body'], 'simple');

        // Publicatie-status normaliseren (toggle + datum → published_at)
        $data = array_merge($data, $request->publicationData());

        // is_published is een form-helper, geen kolom
        $data = Arr::except($data, ['is_published']);

        $data['order'] ??= 0;

        Page::create($data);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Pagina aangemaakt.');
    }

    public function edit(Page $page)
    {
        $this->authorize('update', $page);

        return view('admin.pages.edit', compact('page'));
    }

    public function update(UpdatePageRequest $request, Page $page)
    {
        // Authorization gebeurt al in UpdatePageRequest::authorize()
        $data = $request->validated();

        // Body saneren via mews/purifier 'simple'-profiel
        $data['body'] = Purifier::clean($data['body'], 'simple');

        // Publicatie-status normaliseren
        $data = array_merge($data, $request->publicationData());

        // is_published is een form-helper, geen kolom
        $data = Arr::except($data, ['is_published']);

        $data['order'] ??= 0;

        // Slug blijft bewust unchanged (locked bij update — beslissing #10)
        $page->update($data);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Pagina bijgewerkt.');
    }

    public function destroy(Page $page)
    {
        $this->authorize('delete', $page);

        // Soft delete (Page heeft SoftDeletes-trait, beslissing #9)
        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Pagina verplaatst naar prullenbak.');
    }
}
