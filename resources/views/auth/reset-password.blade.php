@extends('layouts.auth')

@section('title', __('Nieuw wachtwoord instellen'))
@section('heading', __('Nieuw wachtwoord'))
@section('subheading', __('Kies een sterk wachtwoord van minimaal 8 tekens.'))

@section('hero-quote')
    Een nieuwe sleutel voor de voordeur &mdash; je verhalen wachten op je.
@endsection

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="auth-form" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="auth-field">
            <label for="email">{{ __('E-mailadres') }}</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
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
            <label for="password">{{ __('Nieuw wachtwoord') }}</label>
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
            {{ __('Wachtwoord opslaan') }}
        </button>
    </form>
@endsection

@section('footer')
    <a href="{{ route('login') }}">{{ __('Terug naar inloggen') }}</a>
@endsection
