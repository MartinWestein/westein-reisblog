<?php

namespace App\Listeners;

use Illuminate\Auth\Events\PasswordReset;

class MarkEmailVerifiedAfterPasswordReset
{
    /**
     * Zet email_verified_at op now() na een succesvolle password-reset.
     *
     * F4-U4: bij invite-flow bewijst het klikken op de link + wachtwoord
     * instellen dat het adres van de user is. Voor al-geverifieerde users
     * is dit een idempotente no-op.
     */
    public function handle(PasswordReset $event): void
    {
        $user = $event->user;

        if ($user->email_verified_at !== null) {
            return;
        }

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();
    }
}
