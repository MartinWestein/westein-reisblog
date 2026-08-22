<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Route;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class RouteController extends Controller
{
    public function index(): View
    {
        $routes = Route::query()
            ->published()
            ->with(['destination:id,name,slug', 'media'])
            ->orderByDesc('is_featured')
            ->orderByDesc('travel_date')
            ->get();

        return view('routes.index', compact('routes'));
    }

    public function show(Route $route): View
    {
        abort_unless($route->isPublished(), 404);

        $route->load([
            'destination:id,name,slug',
            'waypoints.location:id,destination_id,name,slug,latitude,longitude',
            'waypoints.location.destination:id,name,slug',
            'media',
        ]);

        $relatedPosts = $this->relatedPosts($route);

        return view('routes.show', compact('route', 'relatedPosts'));
    }

    /**
     * Gepubliceerde verhalen uit de bestemming van de route (excl. reistips),
     * nieuwste eerst, max 3. Spiegelt de gerelateerde-posts-aanpak (F5-85).
     */
    private function relatedPosts(Route $route): Collection
    {
        if (! $route->destination_id) {
            return collect();
        }

        return Post::query()
            ->published()
            ->where('destination_id', $route->destination_id)
            ->whereDoesntHave('categories', fn ($q) => $q->where('slug', 'tips'))
            ->with([
                'destination:id,name,slug',
                'location:id,destination_id,name,slug',
                'categories:id,slug',
                'media',
            ])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();
    }
}
