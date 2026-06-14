<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Posts\StorePostRequest;
use App\Http\Requests\Admin\Posts\UpdatePostRequest;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Post::class);

        $query = Post::query()->with(['author', 'categories', 'media']);

        // Zoeken op titel of slug
        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Statusfilter (echte enum-kolom — anders dan Pages)
        $status = $request->string('status', 'all')->value();
        if (in_array($status, ['draft', 'scheduled', 'published', 'archived'], true)) {
            $query->where('status', $status);
        } else {
            $status = 'all';
        }

        // Auteurfilter
        $author = $request->integer('author');
        if ($author) {
            $query->where('user_id', $author);
        }

        // Bestemmingfilter
        $destination = $request->integer('destination');
        if ($destination) {
            $query->where('destination_id', $destination);
        }

        // Sortering — standaard nieuwste eerst (beslissing 4.5)
        $sort = $request->string('sort', 'created_at')->value();
        $direction = $request->string('direction', 'desc')->value() === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, ['title', 'status', 'published_at', 'created_at'], true)) {
            $sort = 'created_at';
        }

        $posts = $query->orderBy($sort, $direction)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Filter-opties — auteurs afgeleid uit posts (geen aanname over User::posts())
        $authorIds = Post::query()->select('user_id')->distinct()->pluck('user_id');
        $authors = User::whereIn('id', $authorIds)->orderBy('name')->get(['id', 'name']);
        $destinations = Destination::orderBy('name')->get(['id', 'name']);

        return view('admin.posts.index', compact(
            'posts', 'sort', 'direction', 'status', 'author', 'destination', 'authors', 'destinations'
        ));
    }

    public function create()
    {
        $this->authorize('create', Post::class);

        return view('admin.posts.create', $this->formData());
    }

    public function store(StorePostRequest $request)
    {
        // Authorization gebeurt al in StorePostRequest::authorize()
        $data = $request->validated();

        // Niet-kolommen apart afhandelen
        $categories = $data['categories'] ?? [];
        $tags = $data['tags'] ?? [];
        $data = Arr::except($data, ['categories', 'tags', 'featured', 'remove_featured']);

        // Slug auto-genereren als leeg gelaten
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Body saneren via mews/purifier 'rich'-profiel (komt in blok D)
        $data['body'] = Purifier::clean($data['body'], 'rich');

        // Publicatie-status normaliseren (status → published_at)
        $data = array_merge($data, $request->publicationData());

        // Auteur = ingelogde gebruiker
        $data['user_id'] = $request->user()->id;

        $post = Post::create($data);

        // Relaties
        $post->categories()->sync($categories);
        $post->syncTagsByName($tags);

        // Featured image (single) — meegestuurd vanaf het create-formulier
        if ($request->hasFile('featured')) {
            $post->addMediaFromRequest('featured')->toMediaCollection('featured');
        }

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('success', 'Post aangemaakt.');
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        $post->load(['categories', 'tags', 'media']);

        return view('admin.posts.edit', array_merge(
            ['post' => $post],
            $this->formData()
        ));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        // Authorization (own/any) gebeurt al in UpdatePostRequest::authorize() → PostPolicy
        $data = $request->validated();

        $categories = $data['categories'] ?? [];
        $tags = $data['tags'] ?? [];
        $removeFeatured = $request->boolean('remove_featured');
        $data = Arr::except($data, ['categories', 'tags', 'featured', 'remove_featured']);

        // Body saneren via 'rich'-profiel
        $data['body'] = Purifier::clean($data['body'], 'rich');

        // Publicatie-status normaliseren
        $data = array_merge($data, $request->publicationData());

        // Slug blijft bewust locked (niet in rules → niet in $data)
        $post->update($data);

        // Relaties opnieuw synchroniseren
        $post->categories()->sync($categories);
        $post->syncTagsByName($tags);

        // Featured image: vervangen of verwijderen
        if ($request->hasFile('featured')) {
            $post->clearMediaCollection('featured');
            $post->addMediaFromRequest('featured')->toMediaCollection('featured');
        } elseif ($removeFeatured) {
            $post->clearMediaCollection('featured');
        }

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post bijgewerkt.');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        // Soft delete (Post heeft SoftDeletes, beslissing #9)
        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post verplaatst naar prullenbak.');
    }

    /**
     * Gedeelde data voor create- en edit-formulier.
     * Locations krijgen destination_id mee zodat de locatie-select in blok E
     * client-side gefilterd kan worden op de gekozen bestemming (UX-helper §3.4).
     *
     * @return array<string, mixed>
     */
    protected function formData(): array
    {
        return [
            'destinations' => Destination::orderBy('name')->get(['id', 'name']),
            'locations' => Location::orderBy('name')->get(['id', 'name', 'destination_id']),
            'categories' => Category::orderBy('order')->get(['id', 'name']),
            'allTags' => Tag::orderBy('name')->pluck('name'),
        ];
    }
}
