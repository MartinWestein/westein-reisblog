<?php

namespace Database\Factories;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Destination>
 */
class DestinationFactory extends Factory
{
    protected $model = Destination::class;

    public function definition(): array
    {
        $name = fake()->unique()->country();

        return [
            'name' => $name,
            'description' => fake()->paragraph(),
            'country_code' => strtoupper(fake()->countryCode()),
        ];
    }
}
