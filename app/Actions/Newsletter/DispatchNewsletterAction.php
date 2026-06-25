<?php

namespace App\Actions\Newsletter;

use App\Jobs\SendNewsletterJob;
use App\Models\Newsletter;
use App\Models\NewsletterSend;
use App\Models\Subscriber;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Throwable;

class DispatchNewsletterAction
{
    /**
     * Initieert de bulk-verzending van een nieuwsbrief.
     *
     * Volgorde (binnen Ã©Ã©n DB::transactie):
     *   1. Snapshot van Subscriber::active()->pluck('id') â€” authoritative
     *      ontvangerslijst op moment van dispatch (F4-N13).
     *   2. Bulk-insert N newsletter_sends rijen, allemaal pending
     *      (sent_at + failed_at = null).
     *   3. Newsletter: status 'draft' â†’ 'sending', recipients_count = N.
     *   4. Bus::batch met SendNewsletterJob per net-aangemaakte rij,
     *      finally() flipt status naar 'sent' (F4-N14).
     *
     * Bij failure tÃ­jdens de transactie (bv. DB-unique-collision wegens
     * race-double-click) rolt alles terug â€” Newsletter blijft 'draft'.
     *
     * @return int aantal subscribers in de batch (= recipients_count)
     *
     * @throws Throwable
     */
    public function execute(Newsletter $newsletter): int
    {
        return DB::transaction(function () use ($newsletter): int {
            $subscriberIds = Subscriber::active()->pluck('id')->all();
            $count = count($subscriberIds);

            $now = now();
            $rows = array_map(fn (int $subscriberId): array => [
                'newsletter_id' => $newsletter->id,
                'subscriber_id' => $subscriberId,
                'sent_at' => null,
                'failed_at' => null,
                'error' => null,
                'opened_at' => null,
                'bounced_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ], $subscriberIds);

            // Eén INSERT met alle rijen (F4-N13). DB-unique-constraint op
            // (newsletter_id, subscriber_id) blokkeert dubbele dispatch
            // automatisch â€” transactie rolt terug bij collisie.
            NewsletterSend::insert($rows);

            // Herhaal de query om de net-toegekende IDs te krijgen.
            // pluck() in dezelfde transactie-context ziet de inserted rows.
            $sendIds = $newsletter->sends()->pluck('id')->all();

            $newsletter->update([
                'status' => Newsletter::STATUS_SENDING,
                'recipients_count' => $count,
            ]);

            $jobs = array_map(
                fn (int $sendId): SendNewsletterJob => new SendNewsletterJob($sendId),
                $sendIds
            );

            // Capture $newsletter via use(); Laravel serialiseert de Eloquent-model
            // naar de batch-store en herlaadt 'em bij callback-execution.
            Bus::batch($jobs)
                ->name("newsletter:{$newsletter->id}")
                ->finally(function (Batch $batch) use ($newsletter): void {
                    $newsletter->update([
                        'status' => Newsletter::STATUS_SENT,
                        'sent_at' => now(),
                    ]);
                })
                ->dispatch();

            return $count;
        });
    }
}
