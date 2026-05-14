<?php

namespace App\Actions\Subscribers;

use App\Models\Subscriber;

class ConfirmSubscriptionAction
{
    /**
     * Bevestig een subscriber via het confirmation_token uit de e-mail.
     *
     * Returns null als het token onbekend is of de subscriber al uitgeschreven.
     * Returns de Subscriber als bevestiging succesvol is, óók als 'ie al
     * eerder bevestigd was (idempotent — dubbele klik op de mail-link is ok).
     */
    public function execute(string $token): ?Subscriber
    {
        $subscriber = Subscriber::where('confirmation_token', $token)->first();

        if (is_null($subscriber)) {
            return null;
        }

        if ($subscriber->isUnsubscribed()) {
            return null;
        }

        if (! $subscriber->isConfirmed()) {
            $subscriber->update([
                'confirmed_at' => now(),
                'confirmation_token' => null, // one-shot: token verbruikt
            ]);
        }

        return $subscriber->fresh();
    }
}
