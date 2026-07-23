<?php

namespace App\Http\Controllers;

use App\Models\Destination;
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
}
