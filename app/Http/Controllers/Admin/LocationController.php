<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Locations\StoreLocationRequest;
use App\Http\Requests\Admin\Locations\UpdateLocationRequest;
use App\Models\Destination;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request, Destination $destination): View
    {
        $this->authorize('viewAny', Location::class);

        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort')->value() ?: 'name';
        $direction = $request->string('direction')->value() === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, ['name', 'created_at'], true)) {
            $sort = 'name';
        }

        $locations = $destination->locations()
            ->with('media')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy($sort, $direction)
            ->paginate(12)
            ->withQueryString();

        return view('admin.locations.index', [
            'destination' => $destination,
            'locations' => $locations,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(Destination $destination): View
    {
        $this->authorize('create', Location::class);

        return view('admin.locations.create', [
            'destination' => $destination,
        ]);
    }

    public function store(StoreLocationRequest $request, Destination $destination): RedirectResponse
    {
        $location = $destination->locations()->create($request->validated());

        return redirect()
            ->route('admin.destinations.locations.edit', [$destination, $location])
            ->with('status', 'Locatie aangemaakt. Voeg hieronder eventueel galerijfoto\'s toe.');
    }

    public function edit(Destination $destination, Location $location): View
    {
        $this->authorize('update', $location);

        $location->load('media');

        return view('admin.locations.edit', [
            'destination' => $destination,
            'location' => $location,
        ]);
    }

    public function update(UpdateLocationRequest $request, Destination $destination, Location $location): RedirectResponse
    {
        $location->update($request->validated());

        return redirect()
            ->route('admin.destinations.locations.index', $destination)
            ->with('status', 'Locatie bijgewerkt.');
    }

    public function destroy(Destination $destination, Location $location): RedirectResponse
    {
        $this->authorize('delete', $location);

        $location->delete(); // soft delete

        return redirect()
            ->route('admin.destinations.locations.index', $destination)
            ->with('status', 'Locatie naar de prullenbak verplaatst.');
    }
}
