<?php

use App\Models\Newsletter;
use App\Models\NewsletterSend;
use App\Models\Subscriber;
use Illuminate\Database\QueryException;

test('subscriber kan worden aangemaakt met factory', function () {
    $sub = Subscriber::factory()->create();

    expect($sub->email)->not->toBeEmpty()
        ->and($sub->unsubscribe_token)->toHaveLength(64);
});

test('unsubscribe_token wordt automatisch gegenereerd bij create', function () {
    $sub = Subscriber::create(['email' => 'test@example.nl']);

    expect($sub->unsubscribe_token)->not->toBeEmpty()
        ->and($sub->unsubscribe_token)->toHaveLength(64);
});

test('unsubscribe_token blijft uniek over meerdere subscribers', function () {
    $tokens = Subscriber::factory()->count(20)->create()->pluck('unsubscribe_token');

    expect($tokens->unique()->count())->toBe(20);
});

test('email is uniek', function () {
    Subscriber::factory()->create(['email' => 'dup@example.nl']);

    expect(fn () => Subscriber::factory()->create(['email' => 'dup@example.nl']))
        ->toThrow(QueryException::class);
});

test('active scope filtert op confirmed en niet-unsubscribed', function () {
    Subscriber::factory()->confirmed()->count(3)->create();
    Subscriber::factory()->pending()->count(2)->create();
    Subscriber::factory()->unsubscribed()->count(1)->create();

    expect(Subscriber::active()->count())->toBe(3);
});

test('pending scope filtert op niet-confirmed en niet-unsubscribed', function () {
    Subscriber::factory()->confirmed()->count(3)->create();
    Subscriber::factory()->pending()->count(2)->create();
    Subscriber::factory()->unsubscribed()->count(1)->create();

    expect(Subscriber::pending()->count())->toBe(2);
});

test('isConfirmed en isUnsubscribed helpers werken correct', function () {
    $confirmed = Subscriber::factory()->confirmed()->create();
    $pending = Subscriber::factory()->pending()->create();
    $unsubbed = Subscriber::factory()->unsubscribed()->create();

    expect($confirmed->isConfirmed())->toBeTrue()
        ->and($confirmed->isUnsubscribed())->toBeFalse()
        ->and($pending->isConfirmed())->toBeFalse()
        ->and($unsubbed->isUnsubscribed())->toBeTrue();
});

test('subscriber heeft sends-relatie naar NewsletterSend', function () {
    $sub = Subscriber::factory()->confirmed()->create();
    $newsletter = Newsletter::factory()->create();
    NewsletterSend::factory()->create([
        'subscriber_id' => $sub->id,
        'newsletter_id' => $newsletter->id,
    ]);

    expect($sub->sends)->toHaveCount(1);
});
