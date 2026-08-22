<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhotoController extends Controller
{
    public function index(Request $request): View
    {
        $destinationSlug = $request->query('bestemming');
        $locationSlug = $request->query('locatie');

        // Bestemmingen met minstens één gallery-foto (voor de filter-pills).
        $destinations = Destination::query()
            ->whereHas('locations.media', fn ($q) => $q->where('collection_name', 'gallery'))
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        // Actieve bestemming (alleen als de slug een geldige, gevulde bestemming is).
        $activeDestination = $destinationSlug
            ? $destinations->firstWhere('slug', $destinationSlug)
            : null;

        // Locatie-pills verschijnen alleen bij een actieve bestemming (progressive).
        $locations = collect();
        if ($activeDestination) {
            $locations = Location::query()
                ->where('destination_id', $activeDestination->id)
                ->whereHas('media', fn ($q) => $q->where('collection_name', 'gallery'))
                ->orderBy('name')
                ->get(['id', 'destination_id', 'name', 'slug']);
        }

        // Foto's via Locations (draagt location + destination-context per foto).
        $photoLocations = Location::query()
            ->whereHas('media', fn ($q) => $q->where('collection_name', 'gallery'))
            ->when($activeDestination, fn ($q) => $q->where('destination_id', $activeDestination->id))
            ->when(
                $activeDestination && $locationSlug,
                fn ($q) => $q->where('slug', $locationSlug)
            )
            ->with(['media', 'destination:id,name,slug'])
            ->orderBy('name')
            ->get();

        $photos = $photoLocations->flatMap(function (Location $location) {
            return $location->getMedia('gallery')->map(fn ($media) => [
                'thumb' => $media->getUrl('medium'),
                'full' => $media->getUrl('large'),
                'name' => $location->name,
                'location' => $location,
            ]);
        })->values();

        return view('photos.index', compact(
            'photos', 'destinations', 'locations',
            'activeDestination', 'destinationSlug', 'locationSlug'
        ));
    }
}
