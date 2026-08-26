@extends('layouts.public')

@section('title', $location->name.' — '.$destination->name)
@section('meta_description', Str::limit(strip_tags($location->description ?? ''), 160))

@section('content')
    <x-public.breadcrumb :items="[
        ['label' => 'Bestemmingen', 'url' => route('destinations.index')],
        ['label' => $destination->name, 'url' => route('destinations.show', $destination)],
        ['label' => $location->name],
    ]" />

    @php
        $gallery = $location->getMedia('gallery');
        $heroMedia = $gallery->first();
        $heroUrl = $heroMedia?->getUrl('large') ?: $heroMedia?->getUrl('medium') ?: $heroMedia?->getUrl();
    @endphp


    @if ($heroUrl)
        @section('og_image', $heroUrl)
    @endif
    
    {{-- Edge-to-edge 2:1 hero uit gallery[0] (F5-40 + F5-48 + F5-52) --}}
    <div class="location-detail__hero">
        @if ($heroUrl)
            <img src="{{ $heroUrl }}"
                 alt="{{ $location->name }}"
                 class="location-detail__hero-image">
        @else
            <div class="location-detail__hero-placeholder" aria-hidden="true">
                <i class="bi bi-image"></i>
            </div>
        @endif
    </div>

    {{-- Intro: section-label + h1 + description-alinea --}}
    <section class="location-detail__intro" aria-labelledby="location-detail-title">
        <div class="container">
            <p class="section-label">Plek</p>
            <h1 id="location-detail-title" class="section-title">{{ $location->name }}</h1>

            @if ($location->description)
                <p class="location-detail__description">{{ $location->description }}</p>
            @endif
        </div>
    </section>

    {{-- Bento-gallery: 1 groot (gallery[0]) + 3 klein (F5-53 + F5-54) --}}
    @if ($gallery->isNotEmpty())
        <section class="location-detail__gallery" aria-labelledby="location-detail-gallery-title">
            <div class="container">
                <p class="section-label">Beelden</p>
                <h2 id="location-detail-gallery-title" class="section-title">Impressies</h2>

                <div class="location-gallery">
                    @foreach ($gallery as $media)
                        @php
                            $isLarge = $loop->first;
                            $tileUrl = $isLarge
                                ? ($media->getUrl('large') ?: $media->getUrl('medium') ?: $media->getUrl())
                                : ($media->getUrl('medium') ?: $media->getUrl());
                            $modifier = $isLarge ? 'location-gallery__item--large' : 'location-gallery__item--small';
                        @endphp
                        <figure class="location-gallery__item {{ $modifier }}">
                            <img src="{{ $tileUrl }}"
                                 alt="{{ $location->name }} — foto {{ $loop->iteration }}"
                                 class="location-gallery__image"
                                 loading="lazy">
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Kaart: waar ligt deze plek? (F5-58 t/m F5-64) --}}
    @if ($location->latitude && $location->longitude)
        <section class="location-detail__map-section" aria-labelledby="location-detail-map-title">
            <div class="container">
                <p class="section-label">Locatie</p>
                <h2 id="location-detail-map-title" class="section-title">Op de kaart</h2>
                <div class="location-detail__map"
                     data-location-map
                     data-lat="{{ $location->latitude }}" data-lng="{{ $location->longitude }}"
                     data-name="{{ $location->name }}"></div>
            </div>
        </section>
    @endif

    {{-- Terug-CTA naar parent destination (F5-55) --}}
    <div class="location-detail__back">
        <div class="container">
            <a href="{{ route('destinations.show', $destination) }}" class="btn btn-accent">
                <i class="bi bi-arrow-left me-1"></i>
                Terug naar {{ $destination->name }}
            </a>
        </div>
    </div>
@endsection
{{-- EOF --}}
