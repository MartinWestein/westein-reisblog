<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Post;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function index(): View
    {
        $destinations = Destination::query()
            ->withCount('locations')
            ->orderByDesc('is_featured')
            ->latest('created_at')
            ->get();

        return view('destinations.index', [
            'destinations' => $destinations,
        ]);
    }

    public function show(Destination $destination): View
    {
        $destination->load(['locations' => fn ($q) => $q->orderBy('id')]);

        // Praktische tips voor deze reis (F5-96 cross-linking, open sinds F5-72):
        // gepubliceerde tips die aan deze bestemming hangen, nieuwste eerst.
        // Linken naar hun canonieke /reistips/{slug}-URL via $post->url().
        $tips = Post::query()
            ->published()
            ->where('destination_id', $destination->id)
            ->whereHas('categories', fn ($q) => $q->where('slug', 'tips'))
            ->with(['author', 'destination', 'location', 'categories', 'media'])
            ->orderByDesc('published_at')
            ->get();

        return view('destinations.show', [
            'destination' => $destination,
            'tips' => $tips,
        ]);
    }
}
