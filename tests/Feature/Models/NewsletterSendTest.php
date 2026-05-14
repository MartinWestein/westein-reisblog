<?php

use App\Models\Newsletter;
use App\Models\NewsletterSend;
use App\Models\Subscriber;
use Illuminate\Database\QueryException;

test('newsletter_send heeft unieke combinatie newsletter+subscriber', function () {
    $newsletter = Newsletter::factory()->create();
    $sub = Subscriber::factory()->confirmed()->create();

    NewsletterSend::factory()->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => $sub->id,
    ]);

    expect(fn () => NewsletterSend::factory()->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => $sub->id,
    ]))->toThrow(QueryException::class);
});

test('newsletter_send cascade-verwijdert bij newsletter delete', function () {
    $newsletter = Newsletter::factory()->create();
    NewsletterSend::factory()->count(3)->create(['newsletter_id' => $newsletter->id]);

    expect(NewsletterSend::count())->toBe(3);

    $newsletter->delete();

    expect(NewsletterSend::count())->toBe(0);
});

test('failed state vult failed_at en error', function () {
    $send = NewsletterSend::factory()->failed()->create();

    expect($send->failed_at)->not->toBeNull()
        ->and($send->error)->not->toBeEmpty()
        ->and($send->sent_at)->toBeNull();
});
