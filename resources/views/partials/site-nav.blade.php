<nav class="main-nav navbar-expand-lg" aria-label="Hoofdnavigatie">
    <div class="container">

        {{-- Logo (links) — absolute URL naar hoofdsite (F5-6) --}}
        <a href="https://ml-westein.nl" class="nav-logo navbar-brand">
            <img src="https://ml-westein.nl/assets/img/logo_v3_192x149.png" alt="ml-westein.nl">
        </a>

        {{-- Hamburger-knop (alleen zichtbaar onder lg) --}}
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#siteNav"
                aria-controls="siteNav"
                aria-expanded="false"
                aria-label="Menu openen of sluiten">
            <i class="bi bi-list"></i>
        </button>

        {{-- Menu-items --}}
        <div class="collapse navbar-collapse justify-content-end" id="siteNav">
            <ul class="nav-menu navbar-nav">
                <li class="nav-item"><a class="nav-link" href="https://ml-westein.nl">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="https://ml-westein.nl/cv.php">C.V.</a></li>
                <li class="nav-item"><a class="nav-link" href="https://genealogie.ml-westein.nl">Genealogie</a></li>
                <li class="nav-item"><a class="nav-link active" href="https://reizen.ml-westein.nl">Reizen</a></li>
                <li class="nav-item"><a class="nav-link" href="https://fotografie.ml-westein.nl">Fotografie</a></li>
                <li class="nav-item"><a class="nav-link" href="https://coding.ml-westein.nl">Coding</a></li>
            </ul>
        </div>

    </div>
</nav>
{{-- EOF --}}
