<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tags\StoreTagRequest;
use App\Http\Requests\Admin\Tags\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Tag::class);

        $query = Tag::query()->withCount('posts');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('name', 'like', "%{$search}%");
        }

        $sort = $request->string('sort', 'name')->value();
        $direction = $request->string('direction', 'asc')->value() === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, ['name', 'posts_count', 'created_at'])) {
            $sort = 'name';
        }

        $tags = $query->orderBy($sort, $direction)
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.tags.index', compact('tags', 'sort', 'direction'));
    }

    public function create()
    {
        $this->authorize('create', Tag::class);

        return view('admin.tags.create');
    }

    public function store(StoreTagRequest $request)
    {
        Tag::create($request->validated());

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Tag aangemaakt.');
    }

    public function edit(Tag $tag)
    {
        $this->authorize('update', $tag);

        return view('admin.tags.edit', compact('tag'));
    }

    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Tag bijgewerkt.');
    }

    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);

        $usageCount = $tag->posts()->count();
        // Bij toekomstige uitbreiding: $usageCount += $tag->locations()->count() etc.

        if ($usageCount > 0) {
            return back()->with('error', "Kan '{$tag->name}' niet verwijderen — wordt nog gebruikt in {$usageCount} item(s).");
        }

        $tag->delete();

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Tag verwijderd.');
    }
}
