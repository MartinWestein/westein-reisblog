<?php

namespace App\Http\Controllers;

use App\Models\Post;

class FeedController extends Controller
{
    public function index()
    {
        $posts = Post::query()
            ->published()
            ->whereDoesntHave('categories', fn ($q) => $q->where('slug', 'tips'))
            ->with(['categories', 'destination', 'location', 'author'])
            ->latest('published_at')
            ->take(20)
            ->get();

        return response()
            ->view('feed.rss', [
                'posts' => $posts,
                'feedTitle' => config('app.name', 'Westein Reisblog'),
                'feedLink' => url('/'),
                'feedDescription' => config('westein.seo.default_description'),
                'feedUrl' => url('/feed'),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
