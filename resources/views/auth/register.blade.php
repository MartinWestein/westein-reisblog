@extends('layouts.auth')

@section('title', __('Account aanmaken'))
@section('heading', __('Word lezer'))
@section('subheading', __('Maak een account aan om te reageren op verhalen en de nieuwsbrief te ontvangen.'))

@section('hero-quote')
    Reizen wordt twee keer beleefd: eerst in werkelijkheid, dan in het vertellen.
@endsection

@section('content')
    <form method="POST" action="{{ route('register') }}" class="auth-form" novalidate>
        @csrf
        @honeypot

        <div class="auth-field">
            <label for="name">{{ __('Naam') }}</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                class="form-control @error('name') is-invalid @enderror"
            >
            @error('name')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="email">{{ __('E-mailadres') }}</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                class="form-control @error('email') is-invalid @enderror"
            >
            @error('email')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password">{{ __('Wachtwoord') }}</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="form-control @error('password') is-invalid @enderror"
            >
            @error('password')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="text-muted d-block mt-1" style="font-size: 0.8125rem;">
                {{ __('Minimaal 8 tekens.') }}
            </small>
        </div>

        <div class="auth-field">
            <label for="password_confirmation">{{ __('Wachtwoord herhalen') }}</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="form-control"
            >
        </div>

        <button type="submit" class="btn btn-primary auth-submit">
            {{ __('Account aanmaken') }}
        </button>

        <p class="auth-meta justify-content-center text-center" style="display: block;">
            {{ __('Door een account aan te maken ga je akkoord met onze') }}
            <a href="{{ url('/privacy') }}">{{ __('privacyverklaring') }}</a>.
        </p>
    </form>
@endsection

@section('footer')
    {{ __('Heb je al een account?') }}
    <a href="{{ route('login') }}">{{ __('Log in') }}</a>
@endsection
