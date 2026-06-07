@extends('layouts.admin')

@section('title', __('Locaties van :name', ['name' => $destination->name]))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.destinations.index') }}">{{ __('Bestemmingen') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.destinations.edit', $destination) }}">{{ $destination->name }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Locaties') }}</span>
@endsection

@section('content')
    @php
        $hero = $destination->getFirstMedia('hero');
        $heroUrl = $hero?->hasGeneratedConversion('medium') ? $hero->getUrl('medium') : $hero?->getUrl();
    @endphp

    <section class="destination-context" @if ($heroUrl) style="background-image: url('{{ $heroUrl }}');" @endif>
        <div class="destination-context__overlay">
            <a href="{{ route('admin.destinations.edit', $destination) }}" class="destination-context__back">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                {{ __('Terug naar bestemming') }}
            </a>
            <h1 class="destination-context__title">{{ $destination->name }}</h1>
            <p class="destination-context__meta">
                {{ trans_choice(':count locatie|:count locaties', $destination->locations()->count()) }}
            </p>
        </div>
    </section>

    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-header__title">{{ __('Locaties') }}</h2>
            <p class="admin-page-header__subtitle">
                {{ __('Steden, dorpen en plekken binnen :name.', ['name' => $destination->name]) }}
            </p>
        </div>
        <a href="{{ route('admin.destinations.locations.create', $destination) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            {{ __('Nieuwe locatie') }}
        </a>
    </div>

    <form method="GET" action="{{ route('admin.destinations.locations.index', $destination) }}" class="family-toolbar">
        <div class="family-toolbar__search">
            <label for="search" class="visually-hidden">{{ __('Zoeken') }}</label>
            <input type="search" id="search" name="search" value="{{ $search }}"
                   class="form-control" placeholder="{{ __('Zoek op naam…') }}">
        </div>

        <div class="family-toolbar__sort">
            <label for="sort">{{ __('Sorteer op') }}</label>
            <select id="sort" name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="name" @selected($sort === 'name')>{{ __('Naam') }}</option>
                <option value="created_at" @selected($sort === 'created_at')>{{ __('Aangemaakt op') }}</option>
            </select>
            <select name="direction" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="asc" @selected($direction === 'asc')>{{ __('Oplopend') }}</option>
                <option value="desc" @selected($direction === 'desc')>{{ __('Aflopend') }}</option>
            </select>

            @if ($search !== '' || $sort !== 'name' || $direction !== 'asc')
                <a href="{{ route('admin.destinations.locations.index', $destination) }}" class="btn btn-sm btn-outline-secondary">
                    {{ __('Reset') }}
                </a>
            @endif

            <noscript><button type="submit" class="btn btn-sm btn-secondary">{{ __('Toepassen') }}</button></noscript>
        </div>
    </form>

    @if ($locations->isEmpty())
        <div class="empty-state">
            <i class="bi bi-pin-map" aria-hidden="true"></i>
            <h2>{{ __('Nog geen locaties') }}</h2>
            <p>{{ __('Voeg de eerste locatie toe aan deze bestemming.') }}</p>
            @if ($search === '')
                <a href="{{ route('admin.destinations.locations.create', $destination) }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    {{ __('Eerste locatie toevoegen') }}
                </a>
            @endif
        </div>
    @else
        <div class="location-grid">
            @foreach ($locations as $location)
                @php
                    $thumb = $location->getFirstMedia('gallery');
                    $thumbUrl = $thumb?->hasGeneratedConversion('thumb') ? $thumb->getUrl('thumb') : $thumb?->getUrl();
                @endphp
                <article class="location-card">
                    <x-admin.card-actions-menu
                        :edit-url="route('admin.destinations.locations.edit', [$destination, $location])"
                        :delete-url="route('admin.destinations.locations.destroy', [$destination, $location])"
                        :delete-confirm="__('Weet je zeker dat je :name naar de prullenbak wilt verplaatsen?', ['name' => $location->name])"
                    />

                    <a href="{{ route('admin.destinations.locations.edit', [$destination, $location]) }}"
                       class="location-card__media"
                       @if ($thumbUrl) style="background-image: url('{{ $thumbUrl }}');" @endif>
                        @unless ($thumbUrl)
                            <span class="location-card__placeholder">
                                <i class="bi bi-image" aria-hidden="true"></i>
                            </span>
                        @endunless
                    </a>

                    <div class="location-card__body">
                        <h2 class="location-card__name">{{ $location->name }}</h2>
                        @if ($location->latitude && $location->longitude)
                            <p class="location-card__coords">
                                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                {{ number_format((float) $location->latitude, 4) }},
                                {{ number_format((float) $location->longitude, 4) }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $locations->links() }}
        </div>
    @endif
@endsection
