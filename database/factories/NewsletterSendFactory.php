<?php

namespace Database\Factories;

use App\Models\Newsletter;
use App\Models\NewsletterSend;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsletterSend>
 */
class NewsletterSendFactory extends Factory
{
    protected $model = NewsletterSend::class;

    public function definition(): array
    {
        return [
            'newsletter_id' => Newsletter::factory(),
            'subscriber_id' => Subscriber::factory()->confirmed(),
            'sent_at' => fake()->dateTimeBetween('-1 month', '-1 day'),
            'failed_at' => null,
            'error' => null,
            'opened_at' => null,
            'bounced_at' => null,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'sent_at' => null,
            'failed_at' => fake()->dateTimeBetween('-1 month', '-1 day'),
            'error' => 'SMTP error: connection timeout',
        ]);
    }
}
