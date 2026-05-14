<?php

use App\Models\Newsletter;
use App\Models\NewsletterSend;
use App\Models\Subscriber;
use App\Models\User;

test('newsletter behoort tot een author', function () {
    $user = User::factory()->create();
    $newsletter = Newsletter::factory()->for($user, 'author')->create();

    expect($newsletter->author->id)->toBe($user->id);
});

test('newsletter heeft sends en recipients relaties', function () {
    $newsletter = Newsletter::factory()->create();
    $subs = Subscriber::factory()->confirmed()->count(3)->create();

    foreach ($subs as $sub) {
        NewsletterSend::factory()->create([
            'newsletter_id' => $newsletter->id,
            'subscriber_id' => $sub->id,
        ]);
    }

    expect($newsletter->sends)->toHaveCount(3)
        ->and($newsletter->recipients)->toHaveCount(3);
});

test('recipients geeft pivot-data terug', function () {
    $newsletter = Newsletter::factory()->create();
    $sub = Subscriber::factory()->confirmed()->create();
    $sentAt = now()->subDays(2);

    NewsletterSend::factory()->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => $sub->id,
        'sent_at' => $sentAt,
    ]);

    $recipient = $newsletter->recipients->first();
    expect($recipient->pivot->sent_at)->not->toBeNull();
});

test('draft sent scheduled scopes filteren op status', function () {
    $user = User::factory()->create();

    Newsletter::factory()->for($user, 'author')->count(2)->create();
    Newsletter::factory()->for($user, 'author')->sent()->count(3)->create();
    Newsletter::factory()->for($user, 'author')->scheduled()->count(1)->create();

    expect(Newsletter::draft()->count())->toBe(2)
        ->and(Newsletter::sent()->count())->toBe(3)
        ->and(Newsletter::scheduled()->count())->toBe(1);
});
