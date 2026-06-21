<x-mail::message>
# {{ __('Welkom bij de Westein Reis Blog') }}

@if ($subscriber->name)
{{ __('Hallo') }} **{{ $subscriber->name }}**,
@else
{{ __('Hallo') }},
@endif

{{ __('Bedankt voor je aanmelding voor onze nieuwsbrief! Klik op de knop hieronder om je e-mailadres te bevestigen.') }}

<x-mail::button :url="$confirmUrl">
{{ __('Bevestig mijn aanmelding') }}
</x-mail::button>

{{ __('Heb je je niet aangemeld? Dan kun je deze e-mail negeren.') }}

{{ __('Groeten') }},<br>
{{ config('app.name') }}

<x-slot:subcopy>
{{ __('Werkt de knop niet? Kopieer deze link in je browser:') }}<br>
<a href="{{ $confirmUrl }}">{{ $confirmUrl }}</a>
</x-slot:subcopy>
</x-mail::message>
