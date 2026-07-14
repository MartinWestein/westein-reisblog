<nav class="blog-nav navbar-expand-lg" aria-label="Blog-navigatie">
    <div class="container">

        {{-- Tekst-brand links, link naar blog-home --}}
        <a href="{{ url('/') }}" class="blog-nav__brand">Westein Reisblog</a>

        {{-- Hamburger-knop (onder lg) --}}
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#blogNav"
                aria-controls="blogNav"
                aria-expanded="false"
                aria-label="Menu openen of sluiten">
            <i class="bi bi-list"></i>
        </button>

        {{-- Menu-items + auth-context --}}
        <div class="collapse navbar-collapse" id="blogNav">
            <ul class="blog-nav__menu navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('bestemmingen*') ? 'active' : '' }}" href="{{ url('/bestemmingen') }}">Bestemmingen</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('reistips*') ? 'active' : '' }}" href="{{ url('/reistips') }}">Reistips</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('reisroutes*') ? 'active' : '' }}" href="{{ url('/reisroutes') }}">Reisroutes</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('fotos*') ? 'active' : '' }}" href="{{ url('/fotos') }}">Foto's</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('contact*') ? 'active' : '' }}" href="{{ url('/contact') }}">Contact</a></li>
            </ul>

            {{-- Auth-context: profiel-dropdown (ingelogd) of Inloggen-link (uitgelogd) --}}
            @auth
                <div class="blog-nav__usermenu" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" class="blog-nav__usermenu-trigger" @click="open = !open" :aria-expanded="open.toString()">
                        <span class="blog-nav__usermenu-name">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down blog-nav__usermenu-chevron"></i>
                    </button>

                    <div class="blog-nav__usermenu-dropdown" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                        <a href="{{ url('/mijn-account') }}" class="blog-nav__usermenu-item">
                            <i class="bi bi-person-circle"></i> Mijn account
                        </a>

                        @if (auth()->user()->hasAnyRole(['admin', 'editor', 'auteur']))
                            <a href="{{ route('admin.home') }}" class="blog-nav__usermenu-item">
                                <i class="bi bi-speedometer2"></i> Naar admin
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="blog-nav__usermenu-item">
                                <i class="bi bi-box-arrow-right"></i> Uitloggen
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="blog-nav__login-link">Inloggen</a>
            @endauth

        </div>

    </div>
</nav>
{{-- EOF --}}
