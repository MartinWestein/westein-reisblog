<?php

namespace Database\Factories;

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
}
