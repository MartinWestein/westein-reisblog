<?php

namespace Database\Factories;

use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Newsletter>
 */
class NewsletterFactory extends Factory
{
    protected $model = Newsletter::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject' => fake()->sentence(5),
            'body' => '<p>'.fake()->paragraphs(4, true).'</p>',
            'status' => 'draft',
            'scheduled_at' => null,
            'sent_at' => null,
            'recipients_count' => 0,
        ];
    }

    public function sent(int $recipientsCount = 0): static
    {
        return $this->state(fn () => [
            'status' => 'sent',
            'sent_at' => fake()->dateTimeBetween('-2 months', '-1 day'),
            'recipients_count' => $recipientsCount,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => 'scheduled',
            'scheduled_at' => fake()->dateTimeBetween('+1 day', '+1 month'),
        ]);
    }
}
