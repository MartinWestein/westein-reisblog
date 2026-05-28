@extends('layouts.admin')

@section('title', __('Familieleden'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Familieleden') }}</span>
@endsection
@section('content')
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-header__title">{{ __('Familieleden') }}</h1>
            <p class="admin-page-header__subtitle">
                {{ __('Beheer de familieprofielen die op de Over ons-pagina verschijnen.') }}
            </p>
        </div>
        <a href="{{ route('admin.family-members.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            {{ __('Nieuw familielid') }}
        </a>
    </div>

    <form method="GET" action="{{ route('admin.family-members.index') }}" class="family-toolbar">
        <div class="family-toolbar__search">
            <label for="search" class="visually-hidden">{{ __('Zoeken') }}</label>
            <input
                type="search"
                id="search"
                name="search"
                value="{{ $search }}"
                class="form-control"
                placeholder="{{ __('Zoek op naam of rol…') }}"
            >
        </div>

        <div class="family-toolbar__sort">
            <label for="sort">{{ __('Sorteer op') }}</label>
            <select id="sort" name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="order" @selected($sort === 'order')>{{ __('Volgorde') }}</option>
                <option value="name" @selected($sort === 'name')>{{ __('Naam') }}</option>
                <option value="role" @selected($sort === 'role')>{{ __('Rol') }}</option>
                <option value="created_at" @selected($sort === 'created_at')>{{ __('Aangemaakt op') }}</option>
            </select>
            <select name="direction" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="asc" @selected($direction === 'asc')>{{ __('Oplopend') }}</option>
                <option value="desc" @selected($direction === 'desc')>{{ __('Aflopend') }}</option>
            </select>

            @if ($search !== '' || $sort !== 'order' || $direction !== 'asc')
                <a href="{{ route('admin.family-members.index') }}" class="btn btn-sm btn-outline-secondary">
                    {{ __('Reset') }}
                </a>
            @endif

            <noscript><button type="submit" class="btn btn-sm btn-secondary">{{ __('Toepassen') }}</button></noscript>
        </div>
    </form>

    @if ($members->isEmpty())
        <div class="empty-state">
            <i class="bi bi-people" aria-hidden="true"></i>
            <h2>{{ __('Nog geen familieleden') }}</h2>
            <p>{{ __('Voeg het eerste familielid toe om de Over ons-pagina te vullen.') }}</p>
            @if ($search === '')
                <a href="{{ route('admin.family-members.create') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    {{ __('Eerste familielid toevoegen') }}
                </a>
            @else
                <p class="mt-3">
                    <a href="{{ route('admin.family-members.index') }}">{{ __('Wis het zoekfilter') }}</a>
                </p>
            @endif
        </div>
    @else
        <div class="family-grid">
            @foreach ($members as $member)
                <article class="family-card" x-data="{ bioOpen: false }">
                    <span class="family-card__order" title="{{ __('Volgorde') }}">#{{ $member->order }}</span>

                    <x-admin.card-actions-menu
                        :edit-url="route('admin.family-members.edit', $member)"
                        :delete-url="route('admin.family-members.destroy', $member)"
                        :delete-confirm="__('Weet je zeker dat je :name wilt verwijderen?', ['name' => $member->name])"
                    />

                    <div class="family-card__avatar-wrap">
                        <x-admin.avatar-initials :member="$member" :size="120" />
                    </div>

                    <h2 class="family-card__name">{{ $member->name }}</h2>

                    @if ($member->role)
                        <p class="family-card__role">{{ $member->role }}</p>
                    @else
                        <p class="family-card__role">&nbsp;</p>
                    @endif

                    @if ($member->user)
                        <span class="family-card__user-badge" title="{{ $member->user->email }}">
                            <i class="bi bi-person-check-fill" aria-hidden="true"></i>
                            {{ __('Gekoppeld') }}
                        </span>
                    @endif

                    @if ($member->bio)
                        <button
                            type="button"
                            class="family-card__bio-toggle"
                            @click="bioOpen = !bioOpen"
                            :aria-expanded="bioOpen.toString()"
                        >
                            <span x-show="!bioOpen">{{ __('Bio tonen') }}</span>
                            <span x-show="bioOpen" x-cloak>{{ __('Bio verbergen') }}</span>
                        </button>

                        <div class="family-card__bio" x-show="bioOpen" x-cloak x-transition>
                            {{ $member->bio }}
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $members->links() }}
        </div>
    @endif
@endsection
