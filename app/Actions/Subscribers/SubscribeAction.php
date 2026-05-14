<?php

namespace App\Actions\Subscribers;

use App\Models\Subscriber;
use Illuminate\Support\Str;

class SubscribeAction
{
    /**
     * Schrijf een e-mailadres in voor de nieuwsbrief.
     *
     * Idempotent: bestaat het adres al, dan geven we de bestaande Subscriber
     * terug met een vers confirmation_token (zodat een herinneringsmail kan).
     * Was 'ie al bevestigd, dan blijft confirmed_at staan en wordt het token
     * niet ververst.
     *
     * @return array{subscriber: Subscriber, was_new: bool, confirmation_token: ?string}
     */
    public function execute(string $email, ?string $name = null): array
    {
        $email = strtolower(trim($email));

        $subscriber = Subscriber::where('email', $email)->first();
        $wasNew = is_null($subscriber);

        if (is_null($subscriber)) {
            // Volledig nieuw
            $token = Str::random(64);

            $subscriber = Subscriber::create([
                'email' => $email,
                'name' => $name,
                'confirmation_token' => $token,
            ]);

            return [
                'subscriber' => $subscriber,
                'was_new' => true,
                'confirmation_token' => $token,
            ];
        }

        // Al uitgeschreven? Re-activeren: token nieuw, unsubscribed_at terug naar null.
        if ($subscriber->isUnsubscribed()) {
            $token = Str::random(64);

            $subscriber->update([
                'name' => $name ?? $subscriber->name,
                'confirmation_token' => $token,
                'confirmed_at' => null,
                'unsubscribed_at' => null,
            ]);

            return [
                'subscriber' => $subscriber->fresh(),
                'was_new' => false,
                'confirmation_token' => $token,
            ];
        }

        // Bestaat al, nog niet bevestigd → vers token, herinneringsmail kan
        if (! $subscriber->isConfirmed()) {
            $token = Str::random(64);

            $subscriber->update([
                'name' => $name ?? $subscriber->name,
                'confirmation_token' => $token,
            ]);

            return [
                'subscriber' => $subscriber->fresh(),
                'was_new' => false,
                'confirmation_token' => $token,
            ];
        }

        // Bestaat al én bevestigd → niks doen. Geen token teruggeven.
        return [
            'subscriber' => $subscriber,
            'was_new' => false,
            'confirmation_token' => null,
        ];
    }
}
