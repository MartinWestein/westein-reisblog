<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Location;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function show(Destination $destination, Location $location): View
    {
        return view('locations.show', [
            'destination' => $destination,
            'location' => $location,
        ]);
    }
}
