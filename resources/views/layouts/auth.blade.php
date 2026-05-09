<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Inloggen')) — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="auth-body">
    <div class="auth-shell">
        <aside
            class="auth-hero"
            style="background-image: url('{{ asset('images/auth-hero.jpg') }}');"
            aria-hidden="true"
>
            <div class="auth-hero__overlay">
                <a href="{{ url('/') }}" class="auth-hero__brand">
                    {{ config('app.name') }}
                </a>
                @hasSection('hero-quote')
                    <blockquote class="auth-hero__quote">
                        @yield('hero-quote')
                    </blockquote>
                @else
                    <blockquote class="auth-hero__quote">
                        Reisverhalen, fotografie en routes &mdash; bewaard voor wie meeleeft.
                    </blockquote>
                @endif
            </div>
        </aside>

        <main class="auth-main">
            <div class="auth-card">
                <header class="auth-card__header">
                    <h1 class="auth-card__title">@yield('heading')</h1>
                    @hasSection('subheading')
                        <p class="auth-card__subheading">@yield('subheading')</p>
                    @endif
                </header>

                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')

                @hasSection('footer')
                    <footer class="auth-card__footer">
                        @yield('footer')
                    </footer>
                @endif
            </div>
        </main>
    </div>
</body>
</html>
