@extends('layouts.public')

@section('title', $route->name)
@section('meta_description', Str::limit(strip_tags($route->description ?? ''), 160))

@section('content')
    @php
        $routeHeroUrl = $route->displayHeroUrl('large') ?? $route->displayHeroUrl('medium');

        $mapWaypoints = $route->waypoints
            ->filter(fn ($wp) => $wp->location && $wp->location->latitude !== null && $wp->location->longitude !== null)
            ->map(fn ($wp) => [
                'lat' => (float) $wp->location->latitude,
                'lng' => (float) $wp->location->longitude,
                'name' => $wp->location->name,
                'notes' => $wp->notes ?? '',
            ])
            ->values();
    @endphp

    {{-- Hero (edge-to-edge 2:1, F5-40/F5-48 + F5-97-plafond) --}}
    <section class="route-detail__hero" aria-hidden="true">
        @if ($routeHeroUrl)
            <img src="{{ $routeHeroUrl }}" alt="{{ $route->name }}" class="route-detail__hero-image">
        @else
            <div class="route-detail__hero-placeholder">
                <i class="bi bi-signpost-split"></i>
            </div>
        @endif
    </section>

    <div class="container">
        <x-public.breadcrumb :items="[
            ['label' => 'Reisroutes', 'url' => route('reisroutes.index')],
            ['label' => $route->name],
        ]" />

        {{-- Intro --}}
        <section class="route-detail__intro">
            <p class="section-label">Reisroute</p>
            <h1 class="route-detail__title">{{ $route->name }}</h1>
            <p class="route-detail__meta">
                @if ($route->travel_date)
                    <i class="bi bi-calendar-event"></i>
                    <time datetime="{{ $route->travel_date->toIso8601String() }}">{{ $route->travel_date->translatedFormat('F Y') }}</time>
                @endif
                @if ($route->destination)
                    <span class="route-detail__meta-sep">·</span>
                    <a href="{{ route('destinations.show', $route->destination) }}">{{ $route->destination->name }}</a>
                @endif
            </p>
            @if ($route->description)
                <p class="route-detail__description">{{ strip_tags($route->description) }}</p>
            @endif
        </section>
    </div>

    {{-- Leaflet-kaart: genummerde markers + polylijn over de waypoints (5.3.b) --}}
    @if ($mapWaypoints->isNotEmpty())
        <section class="route-detail__map-section">
            <div class="container">
                <div class="route-detail__map"
                     data-route-map
                     data-waypoints='@json($mapWaypoints)'></div>
            </div>
        </section>
    @endif

    <div class="container">
        {{-- Waypoint-lijst: genummerd, elk naar zijn location-detail, met notes --}}
        @if ($route->waypoints->isNotEmpty())
            <section class="route-detail__waypoints" aria-labelledby="route-waypoints-title">
                <h2 id="route-waypoints-title" class="route-detail__waypoints-title">De route</h2>
                <ol class="route-waypoint-list">
                    @foreach ($route->waypoints as $waypoint)
                        @if ($waypoint->location)
                            <li class="route-waypoint-list__item">
                                @if ($waypoint->location->destination)
                                    <a class="route-waypoint-list__link"
                                       href="{{ route('locations.show', [$waypoint->location->destination, $waypoint->location]) }}">
                                        {{ $waypoint->location->name }}
                                    </a>
                                @else
                                    <span class="route-waypoint-list__name">{{ $waypoint->location->name }}</span>
                                @endif
                                @if ($waypoint->notes)
                                    <span class="route-waypoint-list__notes">{{ $waypoint->notes }}</span>
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ol>
            </section>
        @endif

        {{-- Verhalen van deze reis (F5-85-model) --}}
        @if ($relatedPosts->isNotEmpty())
            <section class="route-detail__related" aria-labelledby="route-related-title">
                <h2 id="route-related-title" class="section-title">Verhalen van deze reis</h2>
                <div class="post-grid">
                    @foreach ($relatedPosts as $post)
                        <x-public.post-card :post="$post" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Terug-CTA --}}
        <div class="route-detail__back">
            <a href="{{ route('reisroutes.index') }}" class="btn btn-accent">
                <i class="bi bi-arrow-left me-1"></i> Alle reisroutes
            </a>
        </div>
    </div>
@endsection