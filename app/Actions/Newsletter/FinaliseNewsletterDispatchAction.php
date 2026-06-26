<?php

namespace App\Actions\Newsletter;

use App\Models\Newsletter;

class FinaliseNewsletterDispatchAction
{
    /**
     * Markeert een nieuwsbrief als 'sent' na voltooiing van de dispatch-batch.
     *
     * Aangeroepen vanuit DispatchNewsletterAction's Bus::batch()->finally()
     * callback (F4-N14). Idempotent: dubbele uitvoer (failed-batch-replay,
     * handmatige re-trigger) is veilig.
     *
     * Refresht het model voor het idempotency-check zodat we de actuele
     * DB-status zien, ook als de aangereikte instance van een gedeserialiseerde
     * closure-context komt met mogelijk stale attributes.
     */
    public function execute(Newsletter $newsletter): void
    {
        $newsletter->refresh();

        if ($newsletter->status === Newsletter::STATUS_SENT) {
            return;
        }

        $newsletter->update([
            'status' => Newsletter::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }
}
