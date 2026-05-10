<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Tweestapsverificatie') }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body style="background: var(--color-bg, #F8F6F2); min-height: 100vh; padding: 3rem 1.5rem;">
<div style="max-width: 36rem; margin: 0 auto;">

    <a href="{{ url('/dashboard') }}" style="color: var(--color-muted); text-decoration: none; font-size: 0.875rem;">
        ← {{ __('Terug naar dashboard') }}
    </a>

    <h1 style="font-family: 'Playfair Display', serif; font-size: 2.25rem; color: var(--color-text); margin: 1rem 0 .5rem;">
        {{ __('Tweestapsverificatie') }}
    </h1>
    <p style="color: var(--color-muted); margin-bottom: 2rem;">
        {{ __('Voeg een extra beveiligingslaag toe aan je account met een authenticator-app.') }}
    </p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @php
        $user = auth()->user();
        $enabled = ! is_null($user->two_factor_secret);
        $confirmed = ! is_null($user->two_factor_confirmed_at);
    @endphp

    @if (! $enabled)
        {{-- 2FA staat uit: knop om te starten --}}
        <form method="POST" action="{{ route('two-factor.enable') }}">
            @csrf
            <button type="submit" class="btn btn-primary">{{ __('Tweestapsverificatie inschakelen') }}</button>
        </form>

    @elseif ($enabled && ! $confirmed)
        {{-- 2FA gestart, nog niet bevestigd: QR + invoerveld --}}
        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.08); margin-bottom: 1.5rem;">
            <p>{{ __('Scan deze QR-code met je authenticator-app:') }}</p>
            <div style="margin: 1rem 0;">
                {!! $user->twoFactorQrCodeSvg() !!}
            </div>

            <details style="margin: 1rem 0;">
                <summary style="cursor: pointer; color: var(--color-muted); font-size: .875rem;">{{ __('Geen QR-code? Toon de setup-key') }}</summary>
                <p style="font-family: monospace; padding: .5rem; background: #F0EEE8; border-radius: 4px; margin-top: .5rem;">
                    {{ decrypt($user->two_factor_secret) }}
                </p>
            </details>

            <form method="POST" action="{{ route('two-factor.confirm') }}" style="margin-top: 1.5rem;">
                @csrf
                <label style="display: block; font-size: .875rem; margin-bottom: .375rem;">{{ __('Voer de 6-cijferige code uit je app in:') }}</label>
                <input type="text" name="code" inputmode="numeric" required autofocus class="form-control" style="margin-bottom: 1rem;">
                @error('code')
                    <span class="text-danger" style="display: block; font-size: .8125rem; margin-bottom: 1rem;">{{ $message }}</span>
                @enderror
                <button type="submit" class="btn btn-primary">{{ __('Bevestigen') }}</button>
            </form>
        </div>

        <form method="POST" action="{{ route('two-factor.disable') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-link" style="color: var(--color-muted);">{{ __('Annuleer setup') }}</button>
        </form>

    @else
        {{-- 2FA volledig actief: herstelcodes + uitschakelen --}}
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            ✓ {{ __('Tweestapsverificatie is actief.') }}
        </div>

        <h2 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; margin: 1.5rem 0 .75rem;">{{ __('Herstelcodes') }}</h2>
        <p style="color: var(--color-muted); font-size: .875rem;">
            {{ __('Bewaar deze codes op een veilige plek — gebruik ze als je je telefoon kwijt bent.') }}
        </p>
        <div style="background: white; padding: 1rem 1.5rem; border-radius: 8px; font-family: monospace; margin: 1rem 0;">
            @foreach (json_decode(decrypt($user->two_factor_recovery_codes), true) as $code)
                <div>{{ $code }}</div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('two-factor.disable') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">{{ __('Tweestapsverificatie uitschakelen') }}</button>
        </form>
    @endif

</div>
</body>
</html>
