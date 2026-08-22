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
     * Weert tips (F5-94/5.2.c): die hebben hun eigen thuisbasis op /reistips.
     */
    public function index(): View
    {
        $posts = Post::query()
            ->published()
            ->whereDoesntHave('categories', fn ($q) => $q->where('slug', 'tips'))
            ->with(['author', 'destination', 'location', 'categories', 'media'])
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('posts.index', compact('posts'));
    }

    /**
     * Publieke reistips-index op /reistips (F5-72, 5.2.c).
     * Spiegelt index(): alle gepubliceerde posts in de categorie 'Tips',
     * chronologisch nieuwste eerst. Bevat zowel bestemming-gebonden als
     * algemene tips (F5-69).
     */
    public function indexTips(): View
    {
        $tips = Post::query()
            ->published()
            ->whereHas('categories', fn ($q) => $q->where('slug', 'tips'))
            ->with(['author', 'destination', 'location', 'categories', 'media'])
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('reistips.index', compact('tips'));
    }

    /**
     * Location-post detail (F5-74). Weigert tips (F5-72) en niet-gepubliceerd (F5-77).
     */
    public function show(Destination $destination, Location $location, Post $post): View
    {
        abort_if($post->location_id !== $location->id, 404);
        abort_if($post->categories->contains('slug', 'tips'), 404);
        abort_if(! $post->isPublished(), 404);

        return $this->renderDetail($post);
    }

    /**
     * Reistip detail (F5-72). Weigert niet-tips en niet-gepubliceerd.
     */
    public function showTip(Post $post): View
    {
        abort_unless($post->categories->contains('slug', 'tips'), 404);
        abort_if(! $post->isPublished(), 404);

        return $this->renderDetail($post);
    }

    /**
     * Gedeelde detail-render (F5-78): hero, breadcrumb, SEO-meta, body-prose,
     * gerelateerde posts (5.2.b-i) + comments (5.2.b-ii).
     */
    private function renderDetail(Post $post): View
    {
        $post->loadMissing(['author', 'destination', 'location', 'categories', 'media']);

        $related = $this->relatedPosts($post);
        $comments = $this->visibleComments($post);
        $commentsCount = $post->approvedComments()->count();

        return view('posts.show', compact('post', 'related', 'comments', 'commentsCount'));
    }

    /**
     * Gerelateerde posts (F5-85): max 3, nieuwste eerst, alleen gepubliceerd,
     * de post zelf uitgesloten. Location-post -> zelfde destination excl. tips,
     * reistip -> andere reistips.
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

    /**
     * Zichtbare comments (F5-87 + F5-89): top-level + hun replies, oudste eerst.
     * approved voor iedereen + eigen pending voor de ingelogde auteur;
     * rejected/spam altijd verborgen.
     */
    private function visibleComments(Post $post): Collection
    {
        $userId = auth()->id();

        // Herbruikbaar zichtbaarheids-filter, netjes gegroepeerd tussen haakjes.
        $visible = function ($query) use ($userId) {
            $query->where('status', 'approved')
                ->when($userId, function ($q) use ($userId) {
                    $q->orWhere(fn ($inner) => $inner
                        ->where('status', 'pending')
                        ->where('user_id', $userId));
                });
        };

        return $post->comments()
            ->whereNull('parent_id')
            ->where($visible)
            ->with([
                'author',
                'replies' => fn ($q) => $q->where($visible)->with('author')->orderBy('created_at'),
            ])
            ->orderBy('created_at')
            ->get();
    }
}
