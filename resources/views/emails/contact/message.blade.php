<x-mail::message>
# Nieuw contactbericht

Je hebt een nieuw bericht ontvangen via het contactformulier op {{ config('app.name') }}.

**Van:** {{ $senderName }}
**E-mailadres:** {{ $senderEmail }}
**Onderwerp:** {{ $subjectLine }}

---

{{ $messageBody }}

---

Je kunt rechtstreeks op deze mail antwoorden om {{ $senderName }} te bereiken.
</x-mail::message>