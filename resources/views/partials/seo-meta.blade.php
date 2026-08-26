{{-- SEO/OG/Twitter-meta. Leest de per-pagina @section-waarden (title, meta_description,
     canonical, og_image, og_type, article_*) met fallbacks naar config('westein.seo.*').
     Sectie-waarden komen via @section('x', $y) binnen — Blade e()-escapet die al één keer,
     dus we geven ze door met @yield (rauwe echo) i.p.v. {{ }} om dubbel-escaping te vermijden. --}}
@php
    $siteName    = config('app.name', 'Westein Reisblog');
    $ogType      = trim($__env->yieldContent('og_type')) ?: 'website';
    $hasCustomOg = trim($__env->yieldContent('og_image')) !== '';
@endphp
<title>@hasSection('title')@yield('title') — @endif{{ $siteName }}</title>
<meta name="description" content="@hasSection('meta_description')@yield('meta_description')@else{{ config('westein.seo.default_description') }}@endif">
<link rel="canonical" href="@hasSection('canonical')@yield('canonical')@else{{ url()->current() }}@endif">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="@hasSection('title')@yield('title')@else{{ $siteName }}@endif">
<meta property="og:description" content="@hasSection('meta_description')@yield('meta_description')@else{{ config('westein.seo.default_description') }}@endif">
<meta property="og:url" content="@hasSection('canonical')@yield('canonical')@else{{ url()->current() }}@endif">
<meta property="og:image" content="@hasSection('og_image')@yield('og_image')@else{{ asset(config('westein.seo.og_default')) }}@endif">
@unless ($hasCustomOg)
<meta property="og:image:width" content="{{ config('westein.seo.og_image_width') }}">
<meta property="og:image:height" content="{{ config('westein.seo.og_image_height') }}">
@endunless
<meta property="og:image:alt" content="@hasSection('title')@yield('title')@else{{ $siteName }}@endif">
<meta property="og:locale" content="nl_NL">
@if ($ogType === 'article')
@hasSection('article_published_time')
<meta property="article:published_time" content="@yield('article_published_time')">
@endif
@hasSection('article_author')
<meta property="article:author" content="@yield('article_author')">
@endif
@endif

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@hasSection('title')@yield('title')@else{{ $siteName }}@endif">
<meta name="twitter:description" content="@hasSection('meta_description')@yield('meta_description')@else{{ config('westein.seo.default_description') }}@endif">
<meta name="twitter:image" content="@hasSection('og_image')@yield('og_image')@else{{ asset(config('westein.seo.og_default')) }}@endif">