@php
    $siteName    = config('app.name', 'Westein Reisblog');
    $pageTitle   = trim($__env->yieldContent('title'));
    $fullTitle   = $pageTitle !== '' ? $pageTitle.' — '.$siteName : $siteName;
    $description  = trim($__env->yieldContent('meta_description', config('westein.seo.default_description')));
    $canonical    = trim($__env->yieldContent('canonical')) ?: url()->current();
    $ogType       = trim($__env->yieldContent('og_type')) ?: 'website';
    $customOg     = trim($__env->yieldContent('og_image'));
    $ogImage      = $customOg !== '' ? $customOg : asset(config('westein.seo.og_default'));
    $ogIsDefault  = $customOg === '';
    $ogTitle      = $pageTitle !== '' ? $pageTitle : $siteName;
@endphp
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
@if ($ogIsDefault)
<meta property="og:image:width" content="{{ config('westein.seo.og_image_width') }}">
<meta property="og:image:height" content="{{ config('westein.seo.og_image_height') }}">
@endif
<meta property="og:image:alt" content="{{ $ogTitle }}">
<meta property="og:locale" content="nl_NL">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $ogImage }}">