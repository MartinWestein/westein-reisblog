<?php

use App\Jobs\SendNewsletterJob;
use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\NewsletterSend;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

it('verstuurt mail en schrijft sent_at', function () {
    $subscriber = Subscriber::factory()->confirmed()->create();
    $newsletter = Newsletter::factory()->create(['status' => 'sending']);
    $send = NewsletterSend::factory()->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => $subscriber->id,
        'sent_at' => null,
        'failed_at' => null,
    ]);

    (new SendNewsletterJob($send->id))->handle();

    expect($send->fresh()->sent_at)->not->toBeNull()
        ->and($send->fresh()->failed_at)->toBeNull();

    Mail::assertSent(NewsletterMail::class, fn ($mail) => $mail->hasTo($subscriber->email));
});

it('is idempotent bij dubbele run (sent_at al gezet)', function () {
    $subscriber = Subscriber::factory()->confirmed()->create();
    $newsletter = Newsletter::factory()->create(['status' => 'sending']);
    $send = NewsletterSend::factory()->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => $subscriber->id,
        'sent_at' => now()->subMinute(),
    ]);

    (new SendNewsletterJob($send->id))->handle();

    Mail::assertNothingSent();
});

it('skipt subscriber die zich uitschreef tussen dispatch en job-run', function () {
    $subscriber = Subscriber::factory()->unsubscribed()->create();
    $newsletter = Newsletter::factory()->create(['status' => 'sending']);
    $send = NewsletterSend::factory()->create([
        'newsletter_id' => $newsletter->id,
        'subscriber_id' => $subscriber->id,
        'sent_at' => null,
        'failed_at' => null,
    ]);

    (new SendNewsletterJob($send->id))->handle();

    Mail::assertNothingSent();
    expect($send->fresh()->failed_at)->not->toBeNull()
        ->and($send->fresh()->sent_at)->toBeNull()
        ->and($send->fresh()->error)->toBe('Subscriber unsubscribed before delivery');
});

it('returnt zonder fout als NewsletterSend-rij niet meer bestaat', function () {
    // Simuleert race: newsletter hard-deleted â†’ cascade â†’ rij weg vÃ³Ã³r job rent
    expect(fn () => (new SendNewsletterJob(999999))->handle())->not->toThrow(Throwable::class);

    Mail::assertNothingSent();
});

it('schrijft failed_at en error via failed() callback', function () {
    $send = NewsletterSend::factory()->create([
        'sent_at' => null,
        'failed_at' => null,
    ]);

    $exception = new RuntimeException('SMTP connection refused');

    (new SendNewsletterJob($send->id))->failed($exception);

    expect($send->fresh()->failed_at)->not->toBeNull()
        ->and($send->fresh()->error)->toContain('RuntimeException')
        ->and($send->fresh()->error)->toContain('SMTP connection refused');
});
