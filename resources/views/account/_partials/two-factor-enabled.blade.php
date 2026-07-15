<div class="two-factor two-factor--enabled" x-data="{ codesVisible: false }">
    <div class="alert alert-success two-factor__status">
        <i class="bi bi-shield-check"></i>
        Tweefactor-authenticatie is actief.
    </div>

    <div class="two-factor__section">
        <h3 class="two-factor__section-title">Herstelcodes</h3>
        <p class="two-factor__intro">
            Bewaar deze codes op een veilige plek — gebruik ze als je je telefoon kwijt bent.
            Elke code werkt maar één keer.
        </p>

        <button type="button" class="btn btn-outline-secondary btn-sm" @click="codesVisible = !codesVisible">
            <span x-show="!codesVisible">Toon herstelcodes</span>
            <span x-show="codesVisible" x-cloak>Verberg herstelcodes</span>
        </button>

        <div x-show="codesVisible" x-cloak x-transition.opacity class="two-factor__recovery-codes">
            @foreach (json_decode(decrypt($user->two_factor_recovery_codes), true) as $code)
                <div class="two-factor__recovery-code">{{ $code }}</div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="two-factor__regenerate">
            @csrf
            <button type="submit" class="btn btn-link two-factor__regenerate-link"
                    onclick="return confirm('Nieuwe herstelcodes genereren? De huidige codes worden ongeldig.');">
                Herstelcodes opnieuw genereren
            </button>
        </form>
    </div>

    <hr class="two-factor__divider">

    <div class="two-factor__section">
        <h3 class="two-factor__section-title">Uitschakelen</h3>
        <p class="two-factor__intro">
            Als je tweefactor-authenticatie uitschakelt, log je bij een volgend bezoek weer in met alleen je wachtwoord.
        </p>

        <form method="POST" action="{{ route('two-factor.disable') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"
                    onclick="return confirm('Weet je zeker dat je tweefactor-authenticatie wilt uitschakelen?');">
                Tweefactor-authenticatie uitschakelen
            </button>
        </form>
    </div>
</div>
