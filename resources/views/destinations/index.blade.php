@extends('layouts.public')

@section('title', 'Bestemmingen')
@section('meta_description', 'Ontdek alle bestemmingen van familie Westein — verhalen, foto\'s en locaties van onze reizen door Europa en daarbuiten.')

@section('content')
    <section class="destinations-index" aria-labelledby="destinations-index-title">
        <div class="container">
            <p class="section-label">Onze reizen</p>
            <h1 id="destinations-index-title" class="section-title">Bestemmingen</h1>

            <p class="destinations-index__intro">
                {{-- TODO: intro-tekst verfijnen (analoog aan F5-22 homepage-hero placeholder) --}}
                Elke bestemming die je hier vindt hebben we bezocht met de familie. Klik een land aan om verhalen,
                foto's en de plekken die we bezochten te ontdekken.
            </p>

            @if ($destinations->isNotEmpty())
                <div class="destinations-grid">
                    @foreach ($destinations as $destination)
                        <article class="destination-card @if ($destination->is_featured) destination-card--featured @endif"
                                 aria-labelledby="destination-card-title-{{ $destination->id }}">
                            <a href="{{ url('/bestemmingen/'.$destination->slug) }}" class="destination-card__link">

                                <div class="destination-card__image-wrap">
                                    @if ($destination->is_featured)
                                        <span class="destination-card__featured-badge badge bg-warning text-dark">
                                            <i class="bi bi-star-fill" aria-hidden="true"></i>
                                            Uitgelicht
                                        </span>
                                    @endif

                                    @php
                                        $heroUrl = $destination->getFirstMediaUrl('hero', 'medium')
                                            ?: $destination->getFirstMediaUrl('hero');
                                    @endphp

                                    @if ($heroUrl)
                                        <img src="{{ $heroUrl }}"
                                             alt="{{ $destination->name }}"
                                             class="destination-card__image"
                                             loading="lazy">
                                    @else
                                        <div class="destination-card__image-placeholder" aria-hidden="true">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="destination-card__body">
                                    <h2 id="destination-card-title-{{ $destination->id }}" class="destination-card__title">
                                        {{ $destination->name }}
                                    </h2>

                                    @if ($destination->description)
                                        <p class="destination-card__description">
                                            {{ Str::limit(strip_tags($destination->description), 140) }}
                                        </p>
                                    @endif

                                    <p class="destination-card__footer">
                                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                        {{ $destination->locations_count }} {{ $destination->locations_count === 1 ? 'plek' : 'plekken' }}
                                    </p>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="destinations-empty">
                    <p>Er zijn nog geen bestemmingen gepubliceerd.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
{{-- EOF --}}
