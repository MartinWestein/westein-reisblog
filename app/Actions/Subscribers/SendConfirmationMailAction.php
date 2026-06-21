<?php

namespace App\Actions\Subscribers;

use App\Mail\SubscriberConfirmationMail;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendConfirmationMailAction
{
    /**
     * Stuur een (nieuwe) bevestigingsmail.
     * Genereert een fresh token als die ontbreekt.
     * Doet niets als de abonnee al confirmed of unsubscribed is.
     *
     * @return bool true als mail verstuurd, false als skipped
     */
    public function execute(Subscriber $subscriber): bool
    {
        if ($subscriber->isConfirmed() || $subscriber->isUnsubscribed()) {
            return false;
        }

        if (empty($subscriber->confirmation_token)) {
            $subscriber->forceFill(['confirmation_token' => Str::random(64)])->save();
        }

        Mail::send(new SubscriberConfirmationMail($subscriber));

        return true;
    }
}
