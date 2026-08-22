@props(['route'])

@php
    $routeHeroUrl = $route->displayHeroUrl('medium') ?? $route->displayHeroUrl();
@endphp

<article @class(['route-card', 'route-card--featured' => $route->is_featured])>
    <a href="{{ route('reisroutes.show', $route) }}"
       class="route-card__link"
       aria-labelledby="route-card-title-{{ $route->id }}">
        <div class="route-card__image-wrap">
            @if ($route->is_featured)
                <span class="route-card__badge badge bg-warning text-dark">
                    <i class="bi bi-star-fill"></i> Uitgelicht
                </span>
            @endif
            @if ($routeHeroUrl)
                <img src="{{ $routeHeroUrl }}"
                     alt="{{ $route->name }}"
                     class="route-card__image"
                     loading="lazy">
            @else
                <div class="route-card__image-placeholder" aria-hidden="true">
                    <i class="bi bi-geo-alt"></i>
                </div>
            @endif
        </div>
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