<?php

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriberConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Subscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->subscriber->email],
            subject: __('Bevestig je aanmelding voor de nieuwsbrief'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscribers.confirmation',
            with: [
                'subscriber' => $this->subscriber,
                'confirmUrl' => url('/nieuwsbrief/bevestigen/'.$this->subscriber->confirmation_token),
                'unsubscribeUrl' => url('/nieuwsbrief/uitschrijven/'.$this->subscriber->unsubscribe_token),
            ],
        );
    }
}
