<?php

namespace App\Jobs;

use App\Mail\NewsletterMail;
use App\Models\NewsletterSend;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendNewsletterJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Maximaal 3 pogingen: 1 + 2 retries met 1m / 3m / 10m backoff.
     * Beslissing F4-N12 (CLAUDE.md): transient SMTP-hikken bij Hostnet
     * lossen typisch binnen die window op; na een uur geen zinvolle hoop.
     */
    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 180, 600];

    public function __construct(public int $newsletterSendId) {}

    public function retryUntil(): Carbon
    {
        return now()->addHour();
    }

    public function handle(): void
    {
        // Skip stille als de batch is gecancelled (admin trekt verzending in tijdens flight)
        if ($this->batch()?->cancelled()) {
            return;
        }

        $send = NewsletterSend::with(['newsletter', 'subscriber'])->find($this->newsletterSendId);

        // Verdwenen tijdens flight (newsletter hard-deleted â†’ cascade) â€” niets te doen
        if (! $send) {
            return;
        }

        // Idempotent: deze rij is al afgehandeld (succes Ã³f failure)
        if ($send->sent_at !== null || $send->failed_at !== null) {
            return;
        }

        // Subscriber heeft zich uitgeschreven tussen dispatch-snapshot en job-execution
        if ($send->subscriber->isUnsubscribed()) {
            $send->update([
                'failed_at' => now(),
                'error' => 'Subscriber unsubscribed before delivery',
            ]);

            return;
        }

        $unsubscribeUrl = url('/nieuwsbrief/uitschrijven/'.$send->subscriber->unsubscribe_token);

        Mail::to($send->subscriber->email)->send(new NewsletterMail(
            newsletter: $send->newsletter,
            unsubscribeUrl: $unsubscribeUrl,
            isTest: false,
        ));

        $send->update(['sent_at' => now()]);
    }

    /**
     * Wordt door Laravel aangeroepen na de laatste retry-poging is gefaald.
     * Schrijft failed_at + error zodat blok g de status zonder extra query toont.
     */
    public function failed(Throwable $e): void
    {
        $send = NewsletterSend::find($this->newsletterSendId);

        if (! $send) {
            return;
        }

        $send->update([
            'failed_at' => now(),
            'error' => mb_substr($e::class.': '.$e->getMessage(), 0, 65535),
        ]);
    }
}
