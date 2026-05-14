<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'excerpt' => fake()->sentence(15),
            'body' => '<p>'.fake()->paragraphs(3, true).'</p>',
            'featured_image_alt' => null,
            'published_at' => fake()->dateTimeBetween('-6 months', '-1 day'),
            'meta_title' => null,
            'meta_description' => null,
            'order' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['published_at' => null]);
    }
}
