<?php

use App\Actions\Subscribers\ConfirmSubscriptionAction;
use App\Actions\Subscribers\SubscribeAction;
use App\Actions\Subscribers\UnsubscribeAction;
use App\Models\Subscriber;

test('SubscribeAction maakt nieuwe subscriber met confirmation_token', function () {
    $result = app(SubscribeAction::class)->execute('test@voorbeeld.nl', 'Test Persoon');

    expect($result['was_new'])->toBeTrue()
        ->and($result['confirmation_token'])->toHaveLength(64)
        ->and($result['subscriber']->email)->toBe('test@voorbeeld.nl')
        ->and($result['subscriber']->name)->toBe('Test Persoon')
        ->and($result['subscriber']->isConfirmed())->toBeFalse();
});

test('SubscribeAction normaliseert email (lowercase + trim)', function () {
    $result = app(SubscribeAction::class)->execute('  Test@Voorbeeld.NL  ');

    expect($result['subscriber']->email)->toBe('test@voorbeeld.nl');
});

test('SubscribeAction op pending subscriber ververst token zonder duplicaat', function () {
    $first = app(SubscribeAction::class)->execute('test@voorbeeld.nl');
    $second = app(SubscribeAction::class)->execute('test@voorbeeld.nl');

    expect(Subscriber::count())->toBe(1)
        ->and($second['was_new'])->toBeFalse()
        ->and($second['confirmation_token'])->not->toBeNull()
        ->and($second['confirmation_token'])->not->toBe($first['confirmation_token']);
});

test('SubscribeAction op al-bevestigde subscriber returnt null token', function () {
    $first = app(SubscribeAction::class)->execute('test@voorbeeld.nl');
    app(ConfirmSubscriptionAction::class)->execute($first['confirmation_token']);

    $second = app(SubscribeAction::class)->execute('test@voorbeeld.nl');

    expect($second['was_new'])->toBeFalse()
        ->and($second['confirmation_token'])->toBeNull();
});

test('SubscribeAction her-activeert uitgeschreven subscriber', function () {
    $first = app(SubscribeAction::class)->execute('test@voorbeeld.nl');
    app(ConfirmSubscriptionAction::class)->execute($first['confirmation_token']);
    $sub = Subscriber::where('email', 'test@voorbeeld.nl')->first();
    app(UnsubscribeAction::class)->execute($sub->unsubscribe_token);

    $reactivated = app(SubscribeAction::class)->execute('test@voorbeeld.nl');

    expect($reactivated['confirmation_token'])->not->toBeNull()
        ->and($reactivated['subscriber']->isUnsubscribed())->toBeFalse()
        ->and($reactivated['subscriber']->isConfirmed())->toBeFalse();
});

test('ConfirmSubscriptionAction bevestigt subscriber en verbruikt token', function () {
    $sub = app(SubscribeAction::class)->execute('test@voorbeeld.nl');

    $confirmed = app(ConfirmSubscriptionAction::class)->execute($sub['confirmation_token']);

    expect($confirmed)->not->toBeNull()
        ->and($confirmed->isConfirmed())->toBeTrue()
        ->and($confirmed->confirmation_token)->toBeNull();
});

test('ConfirmSubscriptionAction returnt null bij onbekend token', function () {
    $result = app(ConfirmSubscriptionAction::class)->execute(str_repeat('x', 64));

    expect($result)->toBeNull();
});

test('ConfirmSubscriptionAction returnt null voor uitgeschreven subscriber', function () {
    $result = app(SubscribeAction::class)->execute('test@voorbeeld.nl');
    app(ConfirmSubscriptionAction::class)->execute($result['confirmation_token']);
    $sub = Subscriber::first();
    app(UnsubscribeAction::class)->execute($sub->unsubscribe_token);

    // Bewaar het token vóór bevestiging — nu is 'ie al null in DB
    // Dus we creëren een edge case: handmatig token herstellen om scenario te simuleren
    $sub->update([
        'confirmation_token' => 'fake_token_'.str_repeat('y', 53),
    ]);

    $result = app(ConfirmSubscriptionAction::class)->execute('fake_token_'.str_repeat('y', 53));

    expect($result)->toBeNull();
});

test('UnsubscribeAction schrijft subscriber uit', function () {
    $sub = Subscriber::factory()->confirmed()->create();

    $result = app(UnsubscribeAction::class)->execute($sub->unsubscribe_token);

    expect($result->isUnsubscribed())->toBeTrue();
});

test('UnsubscribeAction is idempotent (dubbel klikken)', function () {
    $sub = Subscriber::factory()->confirmed()->create();

    app(UnsubscribeAction::class)->execute($sub->unsubscribe_token);
    $firstUnsubAt = $sub->fresh()->unsubscribed_at;

    sleep(1);

    $result = app(UnsubscribeAction::class)->execute($sub->unsubscribe_token);

    expect($result->isUnsubscribed())->toBeTrue()
        ->and($result->unsubscribed_at->equalTo($firstUnsubAt))->toBeTrue();
});

test('UnsubscribeAction returnt null bij onbekend token', function () {
    $result = app(UnsubscribeAction::class)->execute(str_repeat('z', 64));

    expect($result)->toBeNull();
});
