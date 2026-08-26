@extends('layouts.public')

@section('title', $destination->name)
@section('meta_description', Str::limit(strip_tags($destination->description ?? ''), 160))

@section('content')
    <x-public.json-ld :data="$destination->placeSchema()" />
    
    <x-public.breadcrumb :items="[
        ['label' => 'Bestemmingen', 'url' => route('destinations.index')],
        ['label' => $destination->name],
    ]" />
    @php
        $heroUrl = $destination->getFirstMediaUrl('hero', 'large')
            ?: $destination->getFirstMediaUrl('hero', 'medium')
            ?: $destination->getFirstMediaUrl('hero');
    @endphp


    @if ($heroUrl)
        @section('og_image', $heroUrl)
    @endif
    
    {{-- Edge-to-edge hero (buiten container, F5-40 + F5-48) --}}
    <div class="destination-detail__hero">
        @if ($heroUrl)
            <img src="{{ $heroUrl }}"
                 alt="{{ $destination->name }}"
                 class="destination-detail__hero-image">
        @else
            <div class="destination-detail__hero-placeholder" aria-hidden="true">
                <i class="bi bi-image"></i>
            </div>
        @endif
    </div>

    {{-- Intro: section-label + h1 + description-alinea --}}
    <section class="destination-detail__intro" aria-labelledby="destination-detail-title">
        <div class="container">
            <p class="section-label">Bestemming</p>
            <h1 id="destination-detail-title" class="section-title">{{ $destination->name }}</h1>

            @if ($destination->description)
                <p class="destination-detail__description">{{ $destination->description }}</p>
            @endif
        </div>
    </section>

    {{-- Locations-strook (F5-41 + F5-42 + F5-44) --}}
    @if ($destination->locations->isNotEmpty())
        <section class="destination-detail__locations" aria-labelledby="destination-detail-locations-title">
            <div class="container">
                <p class="section-label">Onderweg</p>
                <h2 id="destination-detail-locations-title" class="section-title">Plekken die we bezochten</h2>

                <div class="locations-grid">
                    @foreach ($destination->locations as $location)
                        <article class="location-card" aria-labelledby="location-card-title-{{ $location->id }}">
                            <a href="{{ route('locations.show', [$destination, $location]) }}" class="location-card__link">                                
                                @php
                                    $locationImageUrl = $location->getFirstMediaUrl('gallery', 'medium')
                                        ?: $location->getFirstMediaUrl('gallery');
                                @endphp

                                <div class="location-card__image-wrap">
                                    @if ($locationImageUrl)
                                        <img src="{{ $locationImageUrl }}"
                                             alt="{{ $location->name }}"
                                             class="location-card__image"
                                             loading="lazy">
                                    @else
                                        <div class="location-card__image-placeholder" aria-hidden="true">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    @endif
                                </div>

                                <h3 id="location-card-title-{{ $location->id }}" class="location-card__title">{{ $location->name }}</h3>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Praktische tips voor deze reis (F5-96 cross-linking, open sinds F5-72).
         Tips hangen aan deze bestemming maar linken naar hun canonieke
         /reistips/{slug}-URL via de post-card ($post->url()). Verborgen bij 0. --}}
    @if ($tips->isNotEmpty())
        <section class="destination-detail__tips" aria-labelledby="destination-detail-tips-title">
            <div class="container">
                <p class="section-label">Praktisch</p>
                <h2 id="destination-detail-tips-title" class="section-title">Reistips voor deze reis</h2>
                <div class="post-grid">
                    @foreach ($tips as $tip)
                        <x-public.post-card :post="$tip" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    
    {{-- Terug-CTA (F5-43) --}}
    <div class="destination-detail__back">
        <div class="container">
            <a href="{{ route('destinations.index') }}" class="btn btn-accent">
                <i class="bi bi-arrow-left me-1"></i>
                Alle bestemmingen bekijken
            </a>
        </div>
    </div>
@endsection
{{-- EOF --}}