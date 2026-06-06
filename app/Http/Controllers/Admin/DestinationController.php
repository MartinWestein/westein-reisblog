<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Destinations\StoreDestinationRequest;
use App\Http\Requests\Admin\Destinations\UpdateDestinationRequest;
use App\Models\Destination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Destination::class);

        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort')->value() ?: 'name';
        $direction = $request->string('direction')->value() === 'desc' ? 'desc' : 'asc';

        $allowedSorts = ['name', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $destinations = Destination::query()
            ->withCount('locations')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy($sort, $direction)
            ->paginate(12)
            ->withQueryString();

        return view('admin.destinations.index', [
            'destinations' => $destinations,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Destination::class);

        return view('admin.destinations.create');
    }

    public function store(StoreDestinationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $destination = Destination::create(
            Arr::except($data, ['hero', 'remove_hero'])
        );

        if ($request->hasFile('hero')) {
            $destination->addMediaFromRequest('hero')->toMediaCollection('hero');
        }

        return redirect()
            ->route('admin.destinations.edit', $destination)
            ->with('status', 'Bestemming aangemaakt. Voeg nu eventueel galerijfoto\'s toe.');
    }

    public function edit(Destination $destination): View
    {
        $this->authorize('update', $destination);

        $destination->load('media');

        return view('admin.destinations.edit', [
            'destination' => $destination,
        ]);
    }

    public function update(UpdateDestinationRequest $request, Destination $destination): RedirectResponse
    {
        $data = $request->validated();

        $destination->update(
            Arr::except($data, ['hero', 'remove_hero'])
        );

        if ($request->boolean('remove_hero')) {
            $destination->clearMediaCollection('hero');
        }

        if ($request->hasFile('hero')) {
            $destination->addMediaFromRequest('hero')->toMediaCollection('hero');
        }

        return redirect()
            ->route('admin.destinations.index')
            ->with('status', 'Bestemming bijgewerkt.');
    }

    public function destroy(Destination $destination): RedirectResponse
    {
        $this->authorize('delete', $destination);

        $destination->delete(); // soft delete

        return redirect()
            ->route('admin.destinations.index')
            ->with('status', 'Bestemming naar de prullenbak verplaatst.');
    }
}
