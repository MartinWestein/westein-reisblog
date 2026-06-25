<?php

use App\Actions\Newsletter\DispatchNewsletterAction;
use App\Jobs\SendNewsletterJob;
use App\Models\Newsletter;
use App\Models\NewsletterSend;
use App\Models\Subscriber;
use Illuminate\Bus\PendingBatch;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Bus;

it('dispatcht batch met Ã©Ã©n SendNewsletterJob per actieve subscriber', function () {
    Bus::fake();

    Subscriber::factory()->count(3)->confirmed()->create();
    Subscriber::factory()->count(2)->pending()->create(); // niet actief
    Subscriber::factory()->count(1)->unsubscribed()->create(); // niet actief

    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    $count = (new DispatchNewsletterAction)->execute($newsletter);

    expect($count)->toBe(3);

    Bus::assertBatched(fn (PendingBatch $batch) => $batch->jobs->count() === 3
        && $batch->jobs->every(fn ($job) => $job instanceof SendNewsletterJob)
        && $batch->name === "newsletter:{$newsletter->id}");
});

it('bulk-inserteert newsletter_sends rijen vÃ³Ã³r batch-dispatch', function () {
    Bus::fake();

    Subscriber::factory()->count(4)->confirmed()->create();

    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    (new DispatchNewsletterAction)->execute($newsletter);

    expect(NewsletterSend::where('newsletter_id', $newsletter->id)->count())->toBe(4)
        ->and(NewsletterSend::where('newsletter_id', $newsletter->id)->whereNotNull('sent_at')->count())->toBe(0)
        ->and(NewsletterSend::where('newsletter_id', $newsletter->id)->whereNotNull('failed_at')->count())->toBe(0);
});

it('zet newsletter status op sending en recipients_count op snapshot-count', function () {
    Bus::fake();

    Subscriber::factory()->count(5)->confirmed()->create();

    $newsletter = Newsletter::factory()->create([
        'status' => Newsletter::STATUS_DRAFT,
        'recipients_count' => 0,
    ]);

    (new DispatchNewsletterAction)->execute($newsletter);

    expect($newsletter->fresh()->status)->toBe(Newsletter::STATUS_SENDING)
        ->and($newsletter->fresh()->recipients_count)->toBe(5)
        ->and($newsletter->fresh()->sent_at)->toBeNull(); // finally() runt niet in Bus::fake()
});

it('returnt nul en doet niets als er geen actieve subscribers zijn', function () {
    Bus::fake();

    Subscriber::factory()->count(2)->pending()->create();

    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    $count = (new DispatchNewsletterAction)->execute($newsletter);

    expect($count)->toBe(0)
        ->and(NewsletterSend::where('newsletter_id', $newsletter->id)->count())->toBe(0)
        ->and($newsletter->fresh()->status)->toBe(Newsletter::STATUS_SENDING); // status flipt wÃ©l

    // Lege batch wordt nog steeds dispatched (finally() flipt alsnog naar sent in productie)
    Bus::assertBatched(fn (PendingBatch $batch) => $batch->jobs->count() === 0);
});

it('rolt alles terug bij dubbele dispatch (race op unique constraint)', function () {
    Bus::fake();

    $subscriber = Subscriber::factory()->confirmed()->create();
    $newsletter = Newsletter::factory()->create(['status' => Newsletter::STATUS_DRAFT]);

    // Eerste dispatch slaagt
    (new DispatchNewsletterAction)->execute($newsletter);

    expect(NewsletterSend::where('newsletter_id', $newsletter->id)->count())->toBe(1);

    // Tweede dispatch op dezelfde newsletter â€” faalt op DB-unique-constraint,
    // transactie rolt terug. Geen tweede rij, geen tweede batch.
    expect(fn () => (new DispatchNewsletterAction)->execute($newsletter))
        ->toThrow(QueryException::class);

    expect(NewsletterSend::where('newsletter_id', $newsletter->id)->count())->toBe(1);
});
