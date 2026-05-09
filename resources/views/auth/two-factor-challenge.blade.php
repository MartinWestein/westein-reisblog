@extends('layouts.auth')

@section('title', __('Tweestapsverificatie'))
@section('heading', __('Tweestapsverificatie'))
@section('subheading', __('Voer de zescijferige code uit je authenticator-app in om in te loggen.'))

@section('hero-quote')
    Twee sleutels &mdash; één voor jou, één in je broekzak.
@endsection

@section('content')
    <div x-data="{ recovery: false }">
        {{-- Reguliere 2FA-code --}}
        <form method="POST" action="{{ route('two-factor.login') }}" class="auth-form" x-show="!recovery" novalidate>
            @csrf

            <div class="auth-field">
                <label for="code">{{ __('Verificatiecode') }}</label>
                <input
                    id="code"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    name="code"
                    autofocus
                    autocomplete="one-time-code"
                    class="form-control @error('code') is-invalid @enderror"
                    style="letter-spacing: 0.4em; font-size: 1.25rem; text-align: center;"
                >
                @error('code')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary auth-submit">
                {{ __('Inloggen') }}
            </button>

            <p class="auth-meta justify-content-center text-center" style="display: block;">
                <a href="#" @click.prevent="recovery = true">
                    {{ __('Authenticator-app niet bij de hand? Gebruik een herstelcode.') }}
                </a>
            </p>
        </form>

        {{-- Recovery-code (fallback) --}}
        <form method="POST" action="{{ route('two-factor.login') }}" class="auth-form" x-show="recovery" x-cloak novalidate>
            @csrf

            <div class="auth-field">
                <label for="recovery_code">{{ __('Herstelcode') }}</label>
                <input
                    id="recovery_code"
                    type="text"
                    name="recovery_code"
                    autocomplete="one-time-code"
                    class="form-control @error('recovery_code') is-invalid @enderror"
                >
                @error('recovery_code')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary auth-submit">
                {{ __('Inloggen met herstelcode') }}
            </button>

            <p class="auth-meta justify-content-center text-center" style="display: block;">
                <a href="#" @click.prevent="recovery = false">
                    {{ __('Terug naar verificatiecode') }}
                </a>
            </p>
        </form>
    </div>
@endsection
