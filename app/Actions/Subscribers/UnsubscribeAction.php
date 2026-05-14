<?php

namespace App\Actions\Subscribers;

use App\Models\Subscriber;

class UnsubscribeAction
{
    /**
     * Schrijf een subscriber uit via het unsubscribe_token uit de mail-footer.
     *
     * Idempotent: dubbel klikken = blijft uitgeschreven, geen fout.
     * Returns null als het token onbekend is.
     */
    public function execute(string $token): ?Subscriber
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->first();

        if (is_null($subscriber)) {
            return null;
        }

        if (! $subscriber->isUnsubscribed()) {
            $subscriber->update([
                'unsubscribed_at' => now(),
            ]);
        }

        return $subscriber->fresh();
    }
}
