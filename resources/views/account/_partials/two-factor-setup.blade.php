<div class="two-factor two-factor--setup">
    <p class="two-factor__intro">
        <strong>Stap 1:</strong> scan onderstaande QR-code met je authenticator-app
        (Google Authenticator, 1Password, Authy, Bitwarden, of vergelijkbaar).
    </p>

    <div class="two-factor__qr">
        {!! $user->twoFactorQrCodeSvg() !!}
    </div>

    <details class="two-factor__setup-key">
        <summary>Geen QR-code kunnen scannen? Toon de setup-key</summary>
        <p class="two-factor__setup-key-value">{{ decrypt($user->two_factor_secret) }}</p>
    </details>

    <hr class="two-factor__divider">

    <p class="two-factor__intro">
        <strong>Stap 2:</strong> voer de 6-cijferige code uit je app in ter bevestiging.
    </p>

    <form method="POST" action="{{ route('two-factor.confirm') }}" class="two-factor__verify">
        @csrf
        <div class="mb-3">
            <label for="two_factor_code" class="form-label">6-cijferige code</label>
            <input type="text"
                   id="two_factor_code"
                   name="code"
                   inputmode="numeric"
                   pattern="[0-9]*"
                   maxlength="6"
                   required
                   autofocus
                   class="form-control two-factor__code-input @error('code', 'confirmTwoFactorAuthentication') is-invalid @enderror">
            @error('code', 'confirmTwoFactorAuthentication')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Bevestigen</button>
    </form>

    <form method="POST" action="{{ route('two-factor.disable') }}" class="two-factor__cancel">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-link two-factor__cancel-link">Setup annuleren</button>
    </form>
</div>
