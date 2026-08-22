<?php

namespace App\Http\Controllers;

use App\Models\Route;
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
            'waypoints.location:id,destination_id,name,latitude,longitude',
            'media',
        ]);

        return view('routes.show', compact('route'));
    }
}
