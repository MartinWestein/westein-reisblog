<?php

namespace App\Actions\Users;

use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class SendUserInvitationAction
{
    /**
     * Genereer een password-reset-token voor de user en stuur de invite-mail.
     * De user kan via de link een wachtwoord instellen. Bij succes zet de
     * PasswordReset-event-listener (blok 4.13.c) email_verified_at op now().
     */
    public function execute(User $user): void
    {
        $token = Password::createToken($user);

        $activationUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        Mail::send(new UserInvitationMail($user, $activationUrl));
    }
}
