<?php

namespace Database\Seeders;

use App\Models\Subscriber;
use Illuminate\Database\Seeder;

class SubscriberDemoSeeder extends Seeder
{
    public function run(): void
    {
        Subscriber::factory()->count(12)->confirmed()->create();
        Subscriber::factory()->count(4)->pending()->create();
        Subscriber::factory()->count(3)->unsubscribed()->create();
    }
}
