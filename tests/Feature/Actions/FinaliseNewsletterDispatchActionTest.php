<?php

use App\Actions\Newsletter\FinaliseNewsletterDispatchAction;
use App\Models\Newsletter;

it('flipt status van sending naar sent en zet sent_at', function () {
    $newsletter = Newsletter::factory()->create([
        'status' => Newsletter::STATUS_SENDING,
        'sent_at' => null,
    ]);

    (new FinaliseNewsletterDispatchAction)->execute($newsletter);

    expect($newsletter->fresh()->status)->toBe(Newsletter::STATUS_SENT)
        ->and($newsletter->fresh()->sent_at)->not->toBeNull();
});

it('is idempotent: re-run op een reeds verzonden nieuwsbrief is een no-op', function () {
    $originalSentAt = now()->subDay();

    $newsletter = Newsletter::factory()->create([
        'status' => Newsletter::STATUS_SENT,
        'sent_at' => $originalSentAt,
    ]);

    (new FinaliseNewsletterDispatchAction)->execute($newsletter);

    // sent_at blijft op de oorspronkelijke waarde â€” geen overschrijving
    expect($newsletter->fresh()->sent_at->timestamp)->toBe($originalSentAt->timestamp);
});

it('refresht het model zodat een stale in-memory instance toch correct werkt', function () {
    $newsletter = Newsletter::factory()->create([
        'status' => Newsletter::STATUS_SENDING,
        'subject' => 'Oorspronkelijke titel',
        'sent_at' => null,
    ]);

    // Simuleer dat een andere process de subject heeft veranderd
    // tussen 't moment dat $newsletter werd geladen en de Action draait
    Newsletter::where('id', $newsletter->id)->update(['subject' => 'Bijgewerkte titel']);

    // $newsletter heeft nog steeds de oude subject in memory
    expect($newsletter->subject)->toBe('Oorspronkelijke titel');

    (new FinaliseNewsletterDispatchAction)->execute($newsletter);

    // Action heeft alleen status + sent_at aangeraakt; de DB-update van subject
    // is intact gebleven
    $fresh = $newsletter->fresh();
    expect($fresh->status)->toBe(Newsletter::STATUS_SENT)
        ->and($fresh->sent_at)->not->toBeNull()
        ->and($fresh->subject)->toBe('Bijgewerkte titel');
});
