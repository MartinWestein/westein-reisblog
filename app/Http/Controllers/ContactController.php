<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Mail\ContactMail;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Contactpagina (F5-113): de contact-Page als intro-tekst + het formulier.
     */
    public function show(): View
    {
        $page = Page::published()->where('slug', 'contact')->first();

        return view('contact.show', [
            'page' => $page,
        ]);
    }

    /**
     * Verstuurt het contactbericht per mail (queued, mail-only) naar het config-adres.
     * Open voor iedereen; honeypot + throttle zitten op de route. Scoped success-flash.
     */
    public function send(StoreContactRequest $request): RedirectResponse
    {
        Mail::to(config('westein.contact.recipient'))
            ->send(new ContactMail(
                senderName: $request->validated('name'),
                senderEmail: $request->validated('email'),
                subjectLine: $request->validated('subject'),
                messageBody: $request->validated('message'),
            ));

        return redirect()
            ->route('contact')
            ->with('contact_success', 'Bedankt voor je bericht! We nemen zo snel mogelijk contact met je op.');
    }
}
