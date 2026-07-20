<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Routes\StoreRouteRequest;
use App\Http\Requests\Admin\Routes\UpdateRouteRequest;
use App\Models\Destination;
use App\Models\Location;
use App\Models\Route;
use App\Models\RouteWaypoint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Mews\Purifier\Facades\Purifier;

class RouteController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Route::class);

        $search = trim((string) $request->query('search', ''));
        $destinationFilter = $request->query('destination');
        $statusFilter = $request->query('status', 'all');
        $sort = $request->query('sort', 'travel_date');
        $direction = $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['name', 'travel_date', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'travel_date';
        }

        $routes = Route::query()
            ->with([
                'destination:id,name,slug',
                'waypoints:id,route_id,location_id,order',
                'waypoints.location:id,name,latitude,longitude',
                'media',
            ])
            ->withCount('waypoints')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($destinationFilter, fn ($q) => $q->whereHas(
                'destination',
                fn ($d) => $d->where('slug', $destinationFilter)
            ))
            ->when($statusFilter === 'published', fn ($q) => $q
                ->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('published_at')->orWhere('published_at', '<=', now());
                }))
            ->when($statusFilter === 'scheduled', fn ($q) => $q
                ->where('is_published', true)
                ->where('published_at', '>', now()))
            ->when($statusFilter === 'draft', fn ($q) => $q->where('is_published', false))
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        $destinations = Destination::orderBy('name')->get(['id', 'name', 'slug']);

        return view('admin.routes.index', compact(
            'routes', 'destinations', 'search', 'destinationFilter',
            'statusFilter', 'sort', 'direction'
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Route::class);

        $destinations = Destination::orderBy('name')->get(['id', 'name']);

        // Locaties worden client-side gefilterd op gekozen bestemming.
        // Voor 't keuze-UX laden we ze allemaal mee (overzichtelijke dataset
        // voor een familieblog; bij grote schaal naar een AJAX-endpoint).
        $locations = Location::orderBy('destination_id')
            ->orderBy('name')
            ->get(['id', 'destination_id', 'name', 'latitude', 'longitude']);

        return view('admin.routes.create', compact('destinations', 'locations'));
    }

    public function store(StoreRouteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $waypoints = $validated['waypoints'] ?? [];

        $data = array_merge(
            Arr::except($validated, ['waypoints', 'hero', 'remove_hero', 'is_published', 'published_at']),
            $request->publicationData()
        );

        $data['description'] = Purifier::clean($data['description'] ?? '', 'simple');

        $route = Route::create($data);

        $this->syncWaypoints($route, $waypoints);
        $this->handleHero($route, $request);

        return redirect()
            ->route('admin.reisroutes.edit', $route)
            ->with('success', "Reisroute «{$route->name}» aangemaakt.");
    }

    public function edit(Route $route): View
    {
        $this->authorize('update', $route);

        $route->load([
            'destination:id,name,slug',
            'waypoints.location:id,destination_id,name',
            'media',
        ]);

        $destinations = Destination::orderBy('name')->get(['id', 'name']);
        $locations = Location::orderBy('destination_id')
            ->orderBy('name')
            ->get(['id', 'destination_id', 'name', 'latitude', 'longitude']);

        return view('admin.routes.edit', compact('route', 'destinations', 'locations'));
    }

    public function update(UpdateRouteRequest $request, Route $route): RedirectResponse
    {
        $validated = $request->validated();
        $waypoints = $validated['waypoints'] ?? [];

        $data = array_merge(
            Arr::except($validated, ['waypoints', 'hero', 'remove_hero', 'is_published', 'published_at']),
            $request->publicationData()
        );

        $data['description'] = Purifier::clean($data['description'] ?? '', 'simple');

        $route->update($data);

        $this->syncWaypoints($route, $waypoints);
        $this->handleHero($route, $request);

        return redirect()
            ->route('admin.reisroutes.edit', $route)
            ->with('success', "Reisroute «{$route->name}» bijgewerkt.");
    }

    public function destroy(Route $route): RedirectResponse
    {
        $this->authorize('delete', $route);

        $name = $route->name;
        $route->delete(); // soft-delete

        return redirect()
            ->route('admin.reisroutes.index')
            ->with('success', "Reisroute «{$name}» verwijderd.");
    }

    /**
     * Sync-strategie: delete-then-recreate.
     * Eenvoudig, schoon, geen FK-/media-/comment-afhankelijkheden op waypoint-IDs.
     * Als 't ooit relevant wordt (waypoint-foto's bv.), refactoren naar upsert.
     */
    private function syncWaypoints(Route $route, array $waypoints): void
    {
        $route->waypoints()->delete();

        foreach (array_values($waypoints) as $index => $waypoint) {
            RouteWaypoint::create([
                'route_id' => $route->id,
                'location_id' => $waypoint['location_id'],
                'order' => $index,
                'notes' => $waypoint['notes'] ?? null,
            ]);
        }
    }

    private function handleHero(Route $route, Request $request): void
    {
        if ($request->boolean('remove_hero')) {
            $route->clearMediaCollection('hero');

            return;
        }

        if ($request->hasFile('hero')) {
            $route->clearMediaCollection('hero');
            $route->addMediaFromRequest('hero')->toMediaCollection('hero');
        }
    }
}
