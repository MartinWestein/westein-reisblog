@extends('layouts.public')

@section('title', 'Mijn account')

@section('content')
    <div class="container account-page">
        <header class="account-page__header">
            <h1>Mijn account</h1>
            <p class="account-page__intro">Beheer je persoonlijke gegevens, wachtwoord en beveiligingsinstellingen.</p>
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif

        {{-- Kaart 1: Persoonlijke gegevens --}}
        <section class="account-card" aria-labelledby="account-card-profile-heading">
            <header class="account-card__header">
                <h2 id="account-card-profile-heading" class="account-card__title">Persoonlijke gegevens</h2>
            </header>

            <div class="account-card__body">
                <form method="POST" action="{{ route('account.update-profile') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Naam</label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name', $user->name) }}"
                               class="form-control @error('name') is-invalid @enderror"
                               required
                               autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-mailadres</label>
                        <p class="account-card__readonly-value">{{ $user->email }}</p>
                        <small class="account-card__hint">E-mailadres wijzigen? Neem contact op met een beheerder.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Rol</label>
                        <div>
                            @foreach ($user->getRoleNames() as $role)
                                <span class="badge account-card__role-badge">{{ $role }}</span>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Gegevens opslaan</button>
                </form>
            </div>
        </section>

        {{-- Kaart 2: Wachtwoord wijzigen --}}
        <section class="account-card" aria-labelledby="account-card-password-heading">
            <header class="account-card__header">
                <h2 id="account-card-password-heading" class="account-card__title">Wachtwoord wijzigen</h2>
            </header>

            <div class="account-card__body">
                @if (session('status') === 'password-updated')
                    <div class="alert alert-success" role="alert">Wachtwoord bijgewerkt.</div>
                @endif

                <form method="POST" action="{{ route('user-password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Huidig wachtwoord</label>
                        <input type="password"
                               id="current_password"
                               name="current_password"
                               class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                               required
                               autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Nieuw wachtwoord</label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                               required
                               autocomplete="new-password">
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Bevestig nieuw wachtwoord</label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               class="form-control"
                               required
                               autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary">Wachtwoord opslaan</button>
                </form>
            </div>
        </section>

        {{-- Kaart 3: Tweefactor-authenticatie --}}
        @php
            $twoFactorEnabled = ! is_null($user->two_factor_secret);
            $twoFactorConfirmed = ! is_null($user->two_factor_confirmed_at);
        @endphp

        <section class="account-card" id="2fa" aria-labelledby="account-card-2fa-heading">
            <header class="account-card__header">
                <h2 id="account-card-2fa-heading" class="account-card__title">Tweefactor-authenticatie</h2>
            </header>

            <div class="account-card__body">
                @if (session('status') === 'two-factor-authentication-enabled')
                    <div class="alert alert-info" role="alert">Tweefactor-authenticatie ingeschakeld. Voltooi de setup hieronder.</div>
                @elseif (session('status') === 'two-factor-authentication-confirmed')
                    <div class="alert alert-success" role="alert">Tweefactor-authenticatie bevestigd en actief.</div>
                @elseif (session('status') === 'two-factor-authentication-disabled')
                    <div class="alert alert-success" role="alert">Tweefactor-authenticatie uitgeschakeld.</div>
                @elseif (session('status') === 'recovery-codes-generated')
                    <div class="alert alert-success" role="alert">Nieuwe herstelcodes gegenereerd.</div>
                @endif

                @if (! $twoFactorEnabled)
                    @include('account._partials.two-factor-disabled')
                @elseif ($twoFactorEnabled && ! $twoFactorConfirmed)
                    @include('account._partials.two-factor-setup', ['user' => $user])
                @else
                    @include('account._partials.two-factor-enabled', ['user' => $user])
                @endif
            </div>
        </section>
    </div>
@endsection
{{-- EOF --}}

