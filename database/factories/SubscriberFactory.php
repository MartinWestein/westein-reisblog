<?php

namespace Database\Factories;

use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Subscriber>
 */
class SubscriberFactory extends Factory
{
    protected $model = Subscriber::class;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'confirmation_token' => Str::random(64),
            'confirmed_at' => null,
            'unsubscribe_token' => Str::random(64),
            'unsubscribed_at' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'confirmation_token' => null,
            'confirmed_at' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'confirmation_token' => Str::random(64),
            'confirmed_at' => null,
        ]);
    }

    public function unsubscribed(): static
    {
        return $this->confirmed()->state(fn () => [
            'unsubscribed_at' => fake()->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }
}
