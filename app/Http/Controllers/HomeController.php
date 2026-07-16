<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Post;
use App\Models\Route;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Toon de homepage: hero, featured destination, laatste posts,
     * featured routes, CTA-strook.
     */
    public function index(): View
    {
        return view('home', [
            'featuredDestination' => Destination::query()
                ->with('media')
                ->latest()
                ->first(),

            'latestPosts' => Post::query()
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->with(['media', 'author', 'destination', 'location'])
                ->latest('published_at')
                ->limit(6)
                ->get(),

            'featuredRoutes' => Route::query()
                ->published()
                ->with(['media', 'destination'])
                ->orderedByTravelDate()
                ->limit(2)
                ->get(),
        ]);
    }
}
