@extends('layouts.auth')

@section('title', __('Bevestig je e-mailadres'))
@section('heading', __('Bevestig je e-mail'))
@section('subheading', __('We hebben een link gestuurd naar je e-mailadres. Klik erop om je account te activeren.'))

@section('hero-quote')
    Eén klik, dan staan de poorten open.
@endsection

@section('content')
    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success" role="alert">
            {{ __('Een nieuwe verificatie-link is verstuurd. Controleer ook je spam-map.') }}
        </div>
    @endif

    <p style="color: var(--color-muted); font-size: 0.9375rem; margin-bottom: 1.5rem;">
        {{ __('Geen mail ontvangen? Klik hieronder om opnieuw te versturen.') }}
    </p>

    <form method="POST" action="{{ route('verification.send') }}" class="auth-form">
        @csrf
        <button type="submit" class="btn btn-primary auth-submit">
            {{ __('Verificatie opnieuw versturen') }}
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" style="margin-top: 1rem;">
        @csrf
        <button
            type="submit"
            class="btn btn-link p-0"
            style="color: var(--color-muted); text-decoration: underline; font-size: 0.875rem;"
        >
            {{ __('Uitloggen') }}
        </button>
    </form>
@endsection
