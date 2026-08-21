<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Publieke blog-index op /verhalen (F5-70, F5-76).
     * Chronologisch (nieuwste eerst), 12 per pagina, alleen gepubliceerd.
     */
    public function index(): View
    {
        $posts = Post::query()
            ->published()
            ->with(['author', 'destination', 'location', 'categories', 'media'])
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('posts.index', compact('posts'));
    }

    /**
     * Location-post detail: /bestemmingen/{destination}/{location}/{post} (F5-74).
     * scopeBindings dwingt de dest->loc-hiërarchie af; hier weigeren we tips
     * (die horen canoniek op /reistips, F5-72) en niet-gepubliceerde posts (F5-77).
     */
    public function show(Destination $destination, Location $location, Post $post): View
    {
        abort_if($post->location_id !== $location->id, 404);
        abort_if($post->categories->contains('slug', 'tips'), 404);
        abort_if(! $post->isPublished(), 404);

        return $this->renderDetail($post);
    }

    /**
     * Reistip detail: /reistips/{post} (F5-72). Weigert niet-tips en niet-gepubliceerd.
     */
    public function showTip(Post $post): View
    {
        abort_unless($post->categories->contains('slug', 'tips'), 404);
        abort_if(! $post->isPublished(), 404);

        return $this->renderDetail($post);
    }

    /**
     * Gedeelde detail-render (F5-78). 5.2.b-i: hero, breadcrumb, SEO-meta,
     * body-prose en gerelateerde posts. Comments volgen in 5.2.b-ii.
     */
    private function renderDetail(Post $post): View
    {
        $post->loadMissing(['author', 'destination', 'location', 'categories', 'media']);

        $related = $this->relatedPosts($post);

        return view('posts.show', compact('post', 'related'));
    }

    /**
     * Gerelateerde posts (F5-85): max 3, nieuwste eerst, alleen gepubliceerd,
     * de post zelf uitgesloten. Location-post -> andere posts uit dezelfde
     * destination (de reis), excl. tips. Reistip -> andere reistips.
     */
    private function relatedPosts(Post $post): Collection
    {
        $isTip = $post->categories->contains('slug', 'tips');

        $query = Post::query()
            ->published()
            ->whereKeyNot($post->getKey())
            ->with(['author', 'destination', 'location', 'categories', 'media'])
            ->orderByDesc('published_at')
            ->limit(3);

        if ($isTip) {
            $query->whereHas('categories', fn ($q) => $q->where('slug', 'tips'));
        } else {
            $query->where('destination_id', $post->destination_id)
                ->whereDoesntHave('categories', fn ($q) => $q->where('slug', 'tips'));
        }

        return $query->get();
    }
}
