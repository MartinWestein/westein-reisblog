<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Route;
use App\Models\RouteWaypoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RouteWaypoint>
 */
class RouteWaypointFactory extends Factory
{
    protected $model = RouteWaypoint::class;

    public function definition(): array
    {
        return [
            'route_id' => Route::factory(),
            'location_id' => Location::factory(),
            'order' => fake()->numberBetween(1, 10),
            'notes' => fake()->optional(0.5)->sentence(),
        ];
    }
}
