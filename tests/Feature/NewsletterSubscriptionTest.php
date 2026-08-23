<?php

use App\Mail\SubscriberConfirmationMail;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['honeypot.enabled' => false]);
    Mail::fake();
});

it('toont het aanmeldformulier', function () {
    get(route('newsletter.show'))
        ->assertOk()
        ->assertSee(route('newsletter.subscribe'), false);
});

it('meldt een nieuw adres aan als pending en verstuurt een bevestigingsmail', function () {
    post(route('newsletter.subscribe'), [
        'email' => 'Nieuw@Example.com',
        'name' => 'Nieuwe Abonnee',
    ])
        ->assertRedirect(route('newsletter.show'))
        ->assertSessionHas('newsletter_success');

    $subscriber = Subscriber::where('email', 'nieuw@example.com')->first();

    expect($subscriber)->not->toBeNull()
        ->and($subscriber->name)->toBe('Nieuwe Abonnee')
        ->and($subscriber->status())->toBe(Subscriber::STATUS_PENDING)
        ->and($subscriber->confirmation_token)->not->toBeNull();

    Mail::assertQueued(SubscriberConfirmationMail::class, fn ($mail) => $mail->subscriber->is($subscriber));
});

it('weigert een ongeldig e-mailadres', function () {
    post(route('newsletter.subscribe'), ['email' => 'geen-email'])
        ->assertSessionHasErrors('email');

    expect(Subscriber::count())->toBe(0);
    Mail::assertNothingQueued();
});

it('maakt geen dubbele rij voor een bestaand onbevestigd adres en herstuurt de bevestiging', function () {
    $existing = Subscriber::create(['email' => 'bekend@example.com']);
    $oldToken = $existing->confirmation_token;

    post(route('newsletter.subscribe'), ['email' => 'bekend@example.com'])
        ->assertRedirect(route('newsletter.show'))
        ->assertSessionHas('newsletter_success');

    expect(Subscriber::where('email', 'bekend@example.com')->count())->toBe(1);

    $existing->refresh();
    expect($existing->confirmation_token)->not->toBe($oldToken)
        ->and($existing->status())->toBe(Subscriber::STATUS_PENDING);

    Mail::assertQueued(SubscriberConfirmationMail::class);
});

it('verstuurt geen mail voor een al bevestigd adres maar toont dezelfde melding', function () {
    $confirmed = Subscriber::create([
        'email' => 'actief@example.com',
        'confirmed_at' => now(),
        'confirmation_token' => null,
    ]);

    post(route('newsletter.subscribe'), ['email' => 'actief@example.com'])
        ->assertRedirect(route('newsletter.show'))
        ->assertSessionHas('newsletter_success');

    expect($confirmed->fresh()->status())->toBe(Subscriber::STATUS_ACTIVE);
    Mail::assertNothingQueued();
});

it('heractiveert een uitgeschreven adres naar pending met nieuwe bevestiging', function () {
    $unsub = Subscriber::create([
        'email' => 'terug@example.com',
        'confirmed_at' => now()->subMonth(),
        'unsubscribed_at' => now()->subWeek(),
        'confirmation_token' => null,
    ]);

    post(route('newsletter.subscribe'), ['email' => 'terug@example.com'])
        ->assertRedirect(route('newsletter.show'));

    $unsub->refresh();
    expect($unsub->status())->toBe(Subscriber::STATUS_PENDING)
        ->and($unsub->confirmed_at)->toBeNull()
        ->and($unsub->unsubscribed_at)->toBeNull()
        ->and($unsub->confirmation_token)->not->toBeNull();

    Mail::assertQueued(SubscriberConfirmationMail::class);
});

it('bevestigt een aanmelding via een geldig token', function () {
    $subscriber = Subscriber::create(['email' => 'bevestig@example.com']);
    $token = $subscriber->confirmation_token;

    get(route('newsletter.confirm', $token))
        ->assertOk()
        ->assertSee('bevestigd');

    $subscriber->refresh();
    expect($subscriber->status())->toBe(Subscriber::STATUS_ACTIVE)
        ->and($subscriber->confirmed_at)->not->toBeNull()
        ->and($subscriber->confirmation_token)->toBeNull();
});

it('toont een neutrale pagina bij een onbekend confirm-token', function () {
    get(route('newsletter.confirm', 'onbekend-token'))
        ->assertOk()
        ->assertSee('ongeldig');
});
// EOF
