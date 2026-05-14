<?php

namespace Database\Factories;

use App\Models\Destination;
use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Route>
 */
class RouteFactory extends Factory
{
    protected $model = Route::class;

    public function definition(): array
    {
        $name = fake()->words(3, true).' roadtrip';

        return [
            'destination_id' => Destination::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraph(2),
            'travel_date' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
