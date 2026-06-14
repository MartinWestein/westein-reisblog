<?php

namespace Database\Factories;

use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $location = Location::factory()->create();

        return [
            'user_id' => User::factory(),
            'destination_id' => $location->destination_id,
            'location_id' => $location->id,
            'title' => fake()->unique()->sentence(6),
            'excerpt' => fake()->sentence(15),
            'body' => fake()->paragraphs(5, true),
            'featured_image_alt' => fake()->sentence(8),
            'status' => 'draft',
            'published_at' => null,
            'views_count' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => 'scheduled',
            'published_at' => now()->addDays(fake()->numberBetween(1, 14)),
        ]);
    }

    /**
     * Post hangt alleen aan een Destination (geen Location).
     * Voor de §3.4-tak "post direct aan destination" en post-niet-Tips zonder location.
     */
    public function forDestinationOnly(): static
    {
        return $this->state(function () {
            $destination = Destination::factory()->create();

            return [
                'destination_id' => $destination->id,
                'location_id' => null,
            ];
        });
    }

    /**
     * Algemene tip — geen destination, geen location.
     * Caller moet zelf de Tips-categorie attachen (afterCreating wordt bewust niet
     * gebruikt om expliciet te houden waar de Tips-koppeling vandaan komt).
     */
    public function tipsGeneral(): static
    {
        return $this->state(fn () => [
            'destination_id' => null,
            'location_id' => null,
        ]);
    }
}
