<div class="two-factor two-factor--disabled">
    <p class="two-factor__intro">
        Voeg een extra beveiligingslaag toe aan je account met een authenticator-app op je telefoon.
        Naast je wachtwoord moet je bij inloggen dan ook een 6-cijferige code invoeren.
    </p>

    <form method="POST" action="{{ route('two-factor.enable') }}">
        @csrf
        <button type="submit" class="btn btn-primary">Tweefactor-authenticatie inschakelen</button>
    </form>
</div>
