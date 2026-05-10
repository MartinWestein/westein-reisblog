<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Beheer') }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body style="background: var(--color-bg, #F8F6F2); min-height: 100vh; padding: 4rem 2rem;">
<div style="max-width: 36rem; margin: 0 auto; text-align: center;">
    <p style="color: var(--color-muted); font-size: .875rem;">{{ __('Beheer') }}</p>
    <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--color-text); margin: .5rem 0 1rem;">
        {{ __('Welkom in het beheer') }}
    </h1>
    <p style="color: var(--color-muted); margin-bottom: 2rem;">
        {{ __('Je bent ingelogd als') }} <strong>{{ auth()->user()->name }}</strong> &middot;
        {{ __('Rollen') }}: {{ auth()->user()->getRoleNames()->join(', ') }}
    </p>
    <p style="color: var(--color-muted); font-size: .9375rem;">
        {{ __('Het echte beheerpaneel volgt in Fase 4.') }}
    </p>
</div>
</body>
</html>
