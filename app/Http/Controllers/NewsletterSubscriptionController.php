<?php

namespace App\Http\Controllers;

use App\Actions\Subscribers\ConfirmSubscriptionAction;
use App\Actions\Subscribers\SendConfirmationMailAction;
use App\Actions\Subscribers\SubscribeAction;
use App\Actions\Subscribers\UnsubscribeAction;
use App\Http\Requests\SubscribeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsletterSubscriptionController extends Controller
{
    /** Aanmeldpagina met formulier. */
    public function show(): View
    {
        return view('newsletter.show');
    }

    /**
     * Verwerk een aanmelding (double-opt-in, stap 1).
     *
     * Leunt op SubscribeAction (idempotent, F4-17) + SendConfirmationMailAction
     * (skipt zelf al-bevestigd/uitgeschreven). We tonen ALTIJD dezelfde melding,
     * ongeacht of het adres nieuw, onbevestigd of al bevestigd is — zo lekt de
     * pagina niet welk adres al bekend is (anti-enumeratie).
     */
    public function store(
        SubscribeRequest $request,
        SubscribeAction $subscribe,
        SendConfirmationMailAction $sendConfirmation,
    ): RedirectResponse {
        $validated = $request->validated();

        $result = $subscribe->execute($validated['email'], $validated['name'] ?? null);
        $sendConfirmation->execute($result['subscriber']);

        return redirect()
            ->route('newsletter.show')
            ->with('newsletter_success', __('Bijna klaar! We hebben je een e-mail met een bevestigingslink gestuurd. Klik op de link in die mail om je aanmelding af te ronden.'));
    }

    /**
     * Bevestig een aanmelding via het confirmation_token uit de mail (stap 2).
     *
     * ConfirmSubscriptionAction is one-shot: bij succes wordt het token gewist,
     * dus een tweede klik op dezelfde link geeft null → neutrale melding.
     */
    public function confirm(string $token, ConfirmSubscriptionAction $confirm): View
    {
        $subscriber = $confirm->execute($token);

        return view('newsletter.confirmed', [
            'subscriber' => $subscriber,
        ]);
    }

    /**
     * Schrijf een subscriber uit via het unsubscribe_token uit de mail-footer.
     *
     * UnsubscribeAction is idempotent en nult het token nooit — een tweede klik
     * blijft dus werken. Onbekend token → null → neutrale melding.
     */
    public function unsubscribe(string $token, UnsubscribeAction $unsubscribe): View
    {
        $subscriber = $unsubscribe->execute($token);

        return view('newsletter.unsubscribed', [
            'subscriber' => $subscriber,
        ]);
    }
}
// EOF
