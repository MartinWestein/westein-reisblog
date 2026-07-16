@extends('layouts.public')

@section('title', 'Onze Reisverhalen')
@section('meta_description', 'Reisverhalen van familie Westein — onze reizen, verhalen en foto\'s')

@section('content')
    {{-- Hero-blok --}}
    <section class="hero-home" aria-labelledby="hero-home-title">
        <div class="container hero-home__grid">
            <div class="hero-home__text">
                <h1 id="hero-home-title" class="hero-home__title">Onze Reisverhalen</h1>
                <p class="hero-home__tagline">Onze reizen, verhalen en foto's</p>
                <p class="hero-home__intro">
                    {{-- TODO: intro-tekst verfijnen (F5-22 placeholder) --}}
                    Sinds jaren delen we hier verhalen en foto's van onze reizen. Van meerdaagse trektochten tot camperroutes met de familie —
                    je vindt hier bestemmingen, tips en herinneringen die we graag met je delen.
                </p>
            </div>

            <div class="hero-home__image">
                <img src="{{ asset('images/hero-home.jpg') }}"
                     alt="Reisfoto — familie Westein onderweg"
                     loading="eager"
                     width="1600"
                     height="1067">
            </div>
        </div>
    </section>

    {{-- Featured destination-blok --}}
    @if ($featuredDestination)
        <section class="featured-destination" aria-labelledby="featured-destination-title">
            <div class="container">
                <p class="section-label">Uitgelichte bestemming</p>

                <div class="featured-destination__grid">
                    <div class="featured-destination__image">
                        @php
                            $heroUrl = $featuredDestination->getFirstMediaUrl('hero', 'medium')
                                ?: $featuredDestination->getFirstMediaUrl('hero');
                        @endphp

                        @if ($heroUrl)
                            <img src="{{ $heroUrl }}"
                                 alt="{{ $featuredDestination->name }}"
                                 loading="lazy">
                        @else
                            <div class="featured-destination__image-placeholder" aria-hidden="true">
                                <i class="bi bi-image"></i>
                            </div>
                        @endif
                    </div>

                    <div class="featured-destination__body">
                        <h2 id="featured-destination-title" class="featured-destination__title">
                            {{ $featuredDestination->name }}
                        </h2>

                        @if ($featuredDestination->description)
                            <p class="featured-destination__description">
                                {{ Str::limit(strip_tags($featuredDestination->description), 240) }}
                            </p>
                        @endif

                        <a href="{{ url('/bestemmingen/'.$featuredDestination->slug) }}" class="btn btn-accent">
                            Meer over {{ $featuredDestination->name }}
                            <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Laatste posts-grid --}}
    @if ($latestPosts->isNotEmpty())
        <section class="latest-posts" aria-labelledby="latest-posts-title">
            <div class="container">
                <p class="section-label">Onze verhalen</p>
                <h2 id="latest-posts-title" class="section-title">Laatste verhalen</h2>

                <div class="post-grid">
                    @foreach ($latestPosts as $post)
                        <article class="post-card">
                            <a href="{{ url('/bestemmingen/'.optional($post->destination)->slug.'/'.$post->slug) }}"
                               class="post-card__link"
                               aria-labelledby="post-card-title-{{ $post->id }}">

                                @php
                                    $coverUrl = $post->getFirstMediaUrl('featured', 'medium')
                                        ?: $post->getFirstMediaUrl('featured');
                                @endphp

                                @if ($coverUrl)
                                    <img src="{{ $coverUrl }}"
                                         alt="{{ $post->featured_image_alt ?: $post->title }}"
                                         class="post-card__image"
                                         loading="lazy">
                                @else
                                    <div class="post-card__image-placeholder" aria-hidden="true">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif

                                <div class="post-card__body">
                                    @if ($post->destination)
                                        <p class="post-card__meta">{{ $post->destination->name }}</p>
                                    @endif

                                    <h3 id="post-card-title-{{ $post->id }}" class="post-card__title">
                                        {{ $post->title }}
                                    </h3>

                                    @if ($post->excerpt)
                                        <p class="post-card__excerpt">{{ Str::limit($post->excerpt, 120) }}</p>
                                    @endif

                                    <p class="post-card__footer">
                                        <span>{{ optional($post->author)->name }}</span>
                                        <span aria-hidden="true">·</span>
                                        <time datetime="{{ $post->published_at->toIso8601String() }}">
                                            {{ $post->published_at->translatedFormat('j F Y') }}
                                        </time>
                                    </p>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Featured routes-blok --}}
    @if ($featuredRoutes->isNotEmpty())
        <section class="featured-routes" aria-labelledby="featured-routes-title">
            <div class="container">
                <p class="section-label">Op stap</p>
                <h2 id="featured-routes-title" class="section-title">Uitgelichte reisroutes</h2>

                <div class="route-grid">
                    @foreach ($featuredRoutes as $route)
                        <article class="route-card">
                            <a href="{{ url('/reisroutes/'.$route->slug) }}"
                               class="route-card__link"
                               aria-labelledby="route-card-title-{{ $route->id }}">

                                @php
                                    $routeUrl = $route->displayHeroUrl('webp-800') ?? $route->displayHeroUrl();
                                @endphp

                                @if ($routeUrl)
                                    <img src="{{ $routeUrl }}"
                                         alt="{{ $route->name }}"
                                         class="route-card__image"
                                         loading="lazy">
                                @else
                                    <div class="route-card__image-placeholder" aria-hidden="true">
                                        <i class="bi bi-geo-alt"></i>
                                    </div>
                                @endif

                                <div class="route-card__body">
                                    @if ($route->destination)
                                        <p class="route-card__meta">{{ $route->destination->name }}</p>
                                    @endif

                                    <h3 id="route-card-title-{{ $route->id }}" class="route-card__title">
                                        {{ $route->name }}
                                    </h3>

                                    @if ($route->description)
                                        <p class="route-card__excerpt">{{ Str::limit(strip_tags($route->description), 140) }}</p>
                                    @endif

                                    @if ($route->travel_date)
                                        <p class="route-card__footer">
                                            <i class="bi bi-calendar-event"></i>
                                            <time datetime="{{ $route->travel_date->toIso8601String() }}">
                                                {{ $route->travel_date->translatedFormat('F Y') }}
                                            </time>
                                        </p>
                                    @endif
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA-strook --}}
    <section class="cta-strip" aria-labelledby="cta-strip-title">
        <div class="container cta-strip__inner">
            <div class="cta-strip__text">
                <h2 id="cta-strip-title" class="cta-strip__title">Ontdek al onze bestemmingen</h2>
                <p class="cta-strip__intro">Blader door alle plekken waar we zijn geweest — van steden tot bergpaden.</p>
            </div>

            <a href="{{ url('/bestemmingen') }}" class="btn btn-accent cta-strip__button">
                Bekijk alle bestemmingen
                <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </section>
@endsection
{{-- EOF --}}
