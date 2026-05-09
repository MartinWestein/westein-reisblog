@extends('layouts.auth')

@section('title', __('Wachtwoord bevestigen'))
@section('heading', __('Even bevestigen'))
@section('subheading', __('Voor je verder gaat, vragen we je je wachtwoord opnieuw in te voeren.'))

@section('hero-quote')
    Een extra slot op de deur &mdash; veiligheid voor jou en je verhalen.
@endsection

@section('content')
    <form method="POST" action="{{ route('password.confirm') }}" class="auth-form" novalidate>
        @csrf

        <div class="auth-field">
            <label for="password">{{ __('Wachtwoord') }}</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autofocus
                autocomplete="current-password"
                class="form-control @error('password') is-invalid @enderror"
            >
            @error('password')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary auth-submit">
            {{ __('Bevestigen') }}
        </button>
    </form>
@endsection
