@extends('layouts.admin')

@section('title', __('Bestemmingen'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Bestemmingen') }}</span>
@endsection

@section('content')
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-header__title">{{ __('Bestemmingen') }}</h1>
            <p class="admin-page-header__subtitle">
                {{ __('Landen en regio\'s waar de familie heen reisde.') }}
            </p>
        </div>
        <a href="{{ route('admin.destinations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            {{ __('Nieuwe bestemming') }}
        </a>
    </div>

    <form method="GET" action="{{ route('admin.destinations.index') }}" class="family-toolbar">
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
                <a href="{{ route('admin.destinations.index') }}" class="btn btn-sm btn-outline-secondary">
                    {{ __('Reset') }}
                </a>
            @endif

            <noscript><button type="submit" class="btn btn-sm btn-secondary">{{ __('Toepassen') }}</button></noscript>
        </div>
    </form>

    @if ($destinations->isEmpty())
        <div class="empty-state">
            <i class="bi bi-geo-alt" aria-hidden="true"></i>
            <h2>{{ __('Nog geen bestemmingen') }}</h2>
            <p>{{ __('Maak je eerste bestemming aan om locaties en posts te kunnen koppelen.') }}</p>
            @if ($search === '')
                <a href="{{ route('admin.destinations.create') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    {{ __('Eerste bestemming toevoegen') }}
                </a>
            @else
                <p class="mt-3">
                    <a href="{{ route('admin.destinations.index') }}">{{ __('Wis het zoekfilter') }}</a>
                </p>
            @endif
        </div>
    @else
        <div class="destination-grid">
            @foreach ($destinations as $destination)
                @php
                    $hero = $destination->getFirstMedia('hero');
                    $heroUrl = $hero?->hasGeneratedConversion('medium') ? $hero->getUrl('medium') : $hero?->getUrl();
                @endphp
                <article class="destination-card">
                    <x-admin.card-actions-menu
                        :edit-url="route('admin.destinations.edit', $destination)"
                        :delete-url="route('admin.destinations.destroy', $destination)"
                        :delete-confirm="__('Weet je zeker dat je :name naar de prullenbak wilt verplaatsen?', ['name' => $destination->name])"
                    />

                    <a href="{{ route('admin.destinations.edit', $destination) }}"
                       class="destination-card__media"
                       @if ($heroUrl) style="background-image: url('{{ $heroUrl }}');" @endif>
                        @unless ($heroUrl)
                            <span class="destination-card__placeholder">
                                <i class="bi bi-image" aria-hidden="true"></i>
                            </span>
                        @endunless
                        @if ($destination->is_featured)
                            <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2">
                                <i class="bi bi-star-fill" aria-hidden="true"></i>
                                {{ __('Uitgelicht') }}
                            </span>
                        @endif
                        <div class="destination-card__overlay">
                            <h2 class="destination-card__name">{{ $destination->name }}</h2>
                            @if ($destination->country_code)
                                <span class="destination-card__country">{{ $destination->country_code }}</span>
                            @endif
                        </div>
                    </a>

                    <div class="destination-card__footer">
                        <span class="destination-card__meta">
                            <i class="bi bi-pin-map" aria-hidden="true"></i>
                            {{ trans_choice(':count locatie|:count locaties', $destination->locations_count) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $destinations->links() }}
        </div>
    @endif
@endsection
