@props([
    'waypoints' => collect(),
    'width' => 80,
    'height' => 56,
])

@php
    // Coördinaten verzamelen uit de waypoint-collectie.
    // Verwacht: $waypoint->location->latitude / ->longitude
    $points = collect($waypoints)
        ->map(fn ($wp) => $wp->location ? [
            'lng' => (float) $wp->location->longitude,
            'lat' => (float) $wp->location->latitude,
        ] : null)
        ->filter()
        ->values();

    $padding = 6; // SVG-padding rondom
    $svg = null;

    if ($points->count() >= 1) {
        $minLng = $points->min('lng');
        $maxLng = $points->max('lng');
        $minLat = $points->min('lat');
        $maxLat = $points->max('lat');

        $spanLng = max(($maxLng - $minLng), 0.0001); // delen-door-nul-guard
        $spanLat = max(($maxLat - $minLat), 0.0001);

        $usableW = $width - 2 * $padding;
        $usableH = $height - 2 * $padding;

        // Aspect-preserving fit: schaal op de strakste as
        $scale = min($usableW / $spanLng, $usableH / $spanLat);

        // Geconservereerde breedte/hoogte na schalen
        $renderedW = $spanLng * $scale;
        $renderedH = $spanLat * $scale;

        // Offset om gecentreerd binnen padding-box te plaatsen
        $offsetX = $padding + ($usableW - $renderedW) / 2;
        $offsetY = $padding + ($usableH - $renderedH) / 2;

        // Project (lng, lat) → SVG-coördinaten (y geinverteerd: noord = boven)
        $coords = $points->map(fn ($p) => [
            'x' => $offsetX + ($p['lng'] - $minLng) * $scale,
            'y' => $offsetY + ($maxLat - $p['lat']) * $scale,
        ]);

        $svg = $coords;
    }
@endphp

<svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 {{ $width }} {{ $height }}"
    width="{{ $width }}"
    height="{{ $height }}"
    class="admin-route-thumb"
    role="img"
    aria-label="Routevoorbeeld"
>
    <rect width="{{ $width }}" height="{{ $height }}" rx="4" class="admin-route-thumb__bg" />

    @if ($svg && $svg->count() >= 2)
        <polyline
            points="{{ $svg->map(fn ($c) => round($c['x'], 1).','.round($c['y'], 1))->implode(' ') }}"
            class="admin-route-thumb__line"
            fill="none"
        />
        @foreach ($svg as $c)
            <circle cx="{{ round($c['x'], 1) }}" cy="{{ round($c['y'], 1) }}" r="1.8" class="admin-route-thumb__dot" />
        @endforeach
    @elseif ($svg && $svg->count() === 1)
        @php $c = $svg->first(); @endphp
        <circle cx="{{ round($c['x'], 1) }}" cy="{{ round($c['y'], 1) }}" r="3" class="admin-route-thumb__dot" />
    @else
        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" class="admin-route-thumb__empty">
            geen route
        </text>
    @endif
</svg>
