@extends('layouts.auth')

@section('title', __('Wachtwoord vergeten'))
@section('heading', __('Wachtwoord vergeten?'))
@section('subheading', __('Geen probleem. Vul je e-mailadres in en we sturen je een link om een nieuw wachtwoord in te stellen.'))

@section('hero-quote')
    Iedere reis kent een omweg &mdash; ook digitaal.
@endsection

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="auth-form" novalidate>
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

        <button type="submit" class="btn btn-primary auth-submit">
            {{ __('Reset-link versturen') }}
        </button>
    </form>
@endsection

@section('footer')
    <a href="{{ route('login') }}">{{ __('Terug naar inloggen') }}</a>
@endsection
