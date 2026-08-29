<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials.seo-meta')

        {{-- Favicon + PWA-manifest --}}
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16.png">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="manifest" href="/site.webmanifest">
        <meta name="theme-color" content="#14213D">

        {{-- Site-brede structured data (WebSite + Organization) --}}
        @include('partials.site-jsonld')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body>
        <a class="skip-link" href="#main-content">Naar hoofdinhoud</a>
        {{-- Site-nav (hoofdsite-look, A-hybrid) — komt in 5.0.a.2 --}}
        @include('partials.site-nav')

        {{-- Blog-nav (dark navy, tekst-brand + menu + profiel-dropdown) — komt in 5.0.a.3 --}}
        @include('partials.blog-nav')

        <main class="public-main" id="main-content">
            @yield('content')
        </main>

        {{-- Footer (dark navy, drie-kolommen) — komt in 5.0.a.4 --}}
        @include('partials.footer')

        @stack('modals')
        @stack('scripts')
    </body>
</html>
{{-- EOF --}}
