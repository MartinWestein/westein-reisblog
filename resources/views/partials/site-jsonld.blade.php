@php
    $__siteUrl = url('/');
    $__siteName = config('app.name', 'Westein Reisblog');
    $__website = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $__siteName,
        'url' => $__siteUrl,
        'inLanguage' => 'nl-NL',
    ];
    $__organization = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $__siteName,
        'url' => $__siteUrl,
        'logo' => asset('images/logo.png'),
        'sameAs' => ['https://ml-westein.nl'],
    ];
@endphp
<x-public.json-ld :data="$__website" />
<x-public.json-ld :data="$__organization" />