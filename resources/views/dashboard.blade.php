<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Dashboard') }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body style="background: var(--color-bg, #F8F6F2); min-height: 100vh; padding: 4rem 2rem;">
    <div style="max-width: 32rem; margin: 0 auto; text-align: center;">
        <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--color-text, #14213D);">
            {{ __('Welkom') }}, {{ auth()->user()->name }}
        </h1>
        <p style="color: var(--color-muted, #5C6779); font-size: 1.125rem; margin: 1rem 0 2rem;">
            {{ __('Je bent ingelogd en geverifieerd. Het echte dashboard volgt in Fase 4.') }}
        </p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="btn btn-primary"
                style="padding: 0.625rem 1.25rem;"
            >
                {{ __('Uitloggen') }}
            </button>
        </form>
    </div>
</body>
</html>
