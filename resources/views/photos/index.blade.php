@extends('layouts.public')

@section('title', 'Foto\'s')
@section('meta_description', 'Foto\'s van de reizen van familie Westein, geordend per bestemming en plek.')

@section('content')
    <section class="photos-index" aria-labelledby="photos-index-title">
        <div class="container">
            <p class="section-label">Beeld</p>
            <h1 id="photos-index-title" class="section-title">Foto's</h1>
            <p class="photos-index__intro">
                {{-- TODO: intro-tekst verfijnen (analoog aan F5-22) --}}
                Een greep uit onze reisfoto's, per bestemming en plek. Klik een foto om 'm groot te bekijken.
            </p>

            {{-- Bestemming-filter --}}
            <nav class="photo-filters" aria-label="Filter op bestemming">
                <a href="{{ route('fotos.index') }}"
                   class="photo-filter-pill {{ ! $activeDestination ? 'is-active' : '' }}">Alle</a>
                @foreach ($destinations as $destination)
                    <a href="{{ route('fotos.index', ['bestemming' => $destination->slug]) }}"
                       class="photo-filter-pill {{ $activeDestination && $activeDestination->id === $destination->id ? 'is-active' : '' }}">
                        {{ $destination->name }}
                    </a>
                @endforeach
            </nav>

            {{-- Locatie-filter (alleen bij actieve bestemming) --}}
            @if ($activeDestination && $locations->isNotEmpty())
                <nav class="photo-filters photo-filters--sub" aria-label="Filter op plek">
                    <a href="{{ route('fotos.index', ['bestemming' => $activeDestination->slug]) }}"
                       class="photo-filter-pill {{ ! $locationSlug ? 'is-active' : '' }}">Alle plekken</a>
                    @foreach ($locations as $location)
                        <a href="{{ route('fotos.index', ['bestemming' => $activeDestination->slug, 'locatie' => $location->slug]) }}"
                           class="photo-filter-pill {{ $locationSlug === $location->slug ? 'is-active' : '' }}">
                            {{ $location->name }}
                        </a>
                    @endforeach
                </nav>
            @endif

            {{-- Foto-grid + lightbox (5.3.c-ii). Tegels blijven links naar de
                 location-detail; Alpine onderschept de klik voor de overlay. --}}
            @if ($photos->isNotEmpty())
                <div x-data="photoLightbox()"
                     x-effect="document.body.classList.toggle('has-lightbox-open', open)">
                    <div class="photo-grid">
                        @foreach ($photos as $photo)
                            <a href="{{ route('locations.show', [$photo['location']->destination, $photo['location']]) }}"
                               class="photo-grid__item"
                               data-full="{{ $photo['full'] }}"
                               data-caption="{{ $photo['name'] }}"
                               @click.prevent="openAt({{ $loop->index }})">
                                <img src="{{ $photo['thumb'] }}"
                                     alt="Foto van {{ $photo['name'] }}"
                                     loading="lazy"
                                     class="photo-grid__image">
                            </a>
                        @endforeach
                    </div>

                    {{-- Overlay --}}
                    <div class="photo-lightbox" x-show="open" x-cloak
                         x-transition.opacity
                         @keydown.escape.window="open && close()"
                         @keydown.arrow-left.window="open && prev()"
                         @keydown.arrow-right.window="open && next()"
                         role="dialog" aria-modal="true" aria-label="Fotoweergave">
                        <div class="photo-lightbox__backdrop" @click="close()"></div>

                        <button type="button" class="photo-lightbox__close" @click="close()" aria-label="Sluiten">
                            <i class="bi bi-x-lg"></i>
                        </button>

                        <button type="button" class="photo-lightbox__nav photo-lightbox__nav--prev"
                                @click="prev()" aria-label="Vorige foto">
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <figure class="photo-lightbox__figure">
                            <img :src="current.full" :alt="'Foto van ' + current.caption" class="photo-lightbox__image">
                            <figcaption class="photo-lightbox__caption">
                                <span x-text="current.caption"></span>
                                <a :href="current.locationUrl" class="photo-lightbox__location-link">Bekijk locatie</a>
                            </figcaption>
                        </figure>

                        <button type="button" class="photo-lightbox__nav photo-lightbox__nav--next"
                                @click="next()" aria-label="Volgende foto">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            @else
                <p class="photos-index__empty">Er zijn nog geen foto's in deze selectie.</p>
            @endif
        </div>
    </section>
@endsection