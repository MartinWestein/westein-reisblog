<footer class="site-footer" role="contentinfo">
    <div class="container">

        <div class="site-footer__grid">

            {{-- Kolom 1: Brand + tagline --}}
            <div class="site-footer__col site-footer__col--brand">
                <div class="site-footer__brand">Westein Reisblog</div>
                <p class="site-footer__tagline">Onze reizen, verhalen en foto's</p>
            </div>

            {{-- Kolom 2: Ontdek --}}
            <div class="site-footer__col">
                <h2 class="site-footer__heading">Ontdek</h2>
                <ul class="site-footer__list">
                    <li><a href="{{ url('/bestemmingen') }}">Bestemmingen</a></li>
                    <li><a href="{{ url('/reistips') }}">Reistips</a></li>
                    <li><a href="{{ url('/reisroutes') }}">Reisroutes</a></li>
                    <li><a href="{{ url('/fotos') }}">Foto's</a></li>
                </ul>
            </div>

            {{-- Kolom 3: Info --}}
            <div class="site-footer__col">
                <h2 class="site-footer__heading">Info</h2>
                <ul class="site-footer__list">
                    <li><a href="{{ url('/over-ons') }}">Over ons</a></li>
                    <li><a href="{{ url('/contact') }}">Contact</a></li>
                    <li><a href="{{ url('/privacy') }}">Privacy</a></li>
                </ul>
            </div>

        </div>

        <div class="site-footer__bottom">
            <p>&copy; {{ date('Y') }} Familie Westein — Alle rechten voorbehouden.</p>
        </div>

    </div>
</footer>
{{-- EOF --}}
