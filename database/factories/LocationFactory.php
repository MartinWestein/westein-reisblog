<?php

namespace Database\Factories;

use App\Models\Destination;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'destination_id' => Destination::factory(),
            'name' => fake()->unique()->city(),
            'description' => fake()->paragraph(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'country_code' => strtoupper(fake()->countryCode()),
        ];
    }
}
