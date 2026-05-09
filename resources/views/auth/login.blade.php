@extends('layouts.auth')

@section('title', __('Inloggen'))
@section('heading', __('Welkom terug'))
@section('subheading', __('Log in om verder te lezen, te reageren en je nieuwsbriefvoorkeuren te beheren'))

@section('hero-quote')
    Welkom terug bij de familie Westein &mdash; pak de draad weer op waar je gebleven was.
@endsection

@section('content')
    <form method="POST" action="{{ route('login') }}" class="auth-form" novalidate>
        @csrf

        <div class="auth-field">
            <label for="email">{{ __('E-mailadres') }}</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
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
                autocomplete="current-password"
                class="form-control @error('password') is-invalid @enderror"
            >
            @error('password')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-meta">
            <label class="form-check m-0">
                <input
                    type="checkbox"
                    name="remember"
                    id="remember"
                    class="form-check-input"
                >
                <span class="form-check-label ms-1">{{ __('Ingelogd blijven') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">
                    {{ __('Wachtwoord vergeten?') }}
                </a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary auth-submit">
            {{ __('Inloggen') }}
        </button>
    </form>
@endsection

@section('footer')
    {{ __('Nog geen account?') }}
    <a href="{{ route('register') }}">{{ __('Maak er een aan') }}</a>
@endsection
