@extends('layouts.public')

@section('title', $route->name)
@section('meta_description', Str::limit(strip_tags($route->description ?? ''), 160))

@section('content')
    @php
        $routeHeroUrl = $route->displayHeroUrl('large') ?? $route->displayHeroUrl('medium');
    @endphp

    {{-- Hero (edge-to-edge, F5-40/F5-48-patroon; hoogte-plafond F5-97 volgt in SCSS) --}}
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
                    <span class="route-detail__meta-sep">·</span> {{ $route->destination->name }}
                @endif
            </p>
            @if ($route->description)
                <p class="route-detail__description">{{ strip_tags($route->description) }}</p>
            @endif
        </section>

        {{-- Waypoints — kale geordende namenlijst. 5.3.b maakt hier de Leaflet-polylijn + notes + location-links van. --}}
        @if ($route->waypoints->isNotEmpty())
            <section class="route-detail__waypoints" aria-labelledby="route-waypoints-title">
                <h2 id="route-waypoints-title" class="route-detail__waypoints-title">De route</h2>
                <ol class="route-waypoint-list">
                    @foreach ($route->waypoints as $waypoint)
                        @if ($waypoint->location)
                            <li class="route-waypoint-list__item">{{ $waypoint->location->name }}</li>
                        @endif
                    @endforeach
                </ol>
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