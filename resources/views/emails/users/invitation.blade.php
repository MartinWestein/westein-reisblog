<x-mail::message>
# {{ __('Welkom, :name!', ['name' => $user->name]) }}

{{ __('Er is een account voor je aangemaakt op :app. Klik op de knop hieronder om je account te activeren en een wachtwoord in te stellen.', ['app' => config('app.name')]) }}

<x-mail::button :url="$activationUrl">
{{ __('Account activeren') }}
</x-mail::button>

{{ __('Deze link is 60 minuten geldig. Heb je hem gemist? Vraag dan een nieuwe uitnodiging aan via de beheerder.') }}

{{ __('Als je dit niet had verwacht, kun je deze mail negeren.') }}

{{ __('Met vriendelijke groet,') }}
{{ config('app.name') }}
</x-mail::message>
