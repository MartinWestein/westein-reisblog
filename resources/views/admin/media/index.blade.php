@extends('layouts.admin')

@section('title', 'Media')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Media</h1>
            <p class="text-muted mb-0">Projectbreed overzicht van content-foto's.</p>
        </div>
        <div class="text-muted small">
            {{ $media->total() }} {{ Str::plural('item', $media->total()) }}
        </div>
    </div>

    {{-- Filter-balk --}}
    <form method="GET" action="{{ route('admin.media.index') }}" class="card card-body mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="filter-collection" class="form-label small text-muted mb-1">Collectie</label>
                <select name="collection" id="filter-collection" class="form-select form-select-sm">
                    <option value="">Alle collecties</option>
                    @foreach(\App\Services\Media\MediaQueryBuilder::ALLOWED_COLLECTIONS as $col)
                        <option value="{{ $col }}" @selected($collection === $col)>{{ $col }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="filter-owner" class="form-label small text-muted mb-1">Eigenaar-type</label>
                <select name="owner_type" id="filter-owner" class="form-select form-select-sm">
                    <option value="">Alle eigenaren</option>
                    @foreach($ownerTypes as $type)
                        <option value="{{ $type }}" @selected($ownerType === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="filter-q" class="form-label small text-muted mb-1">Zoek op bestandsnaam</label>
                <input type="search" name="q" id="filter-q" value="{{ $q }}" class="form-control form-control-sm" placeholder="bv. venetie">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                @if($collection || $ownerType || $q)
                    <a href="{{ route('admin.media.index') }}" class="btn btn-sm btn-outline-secondary" title="Filters wissen">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </div>

        {{-- Sort behouden bij filter-submit --}}
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="direction" value="{{ $direction }}">
    </form>

    {{-- Sort-balk --}}
    <div class="d-flex justify-content-end gap-3 mb-3 small">
        <span class="text-muted">Sorteer:</span>
        <x-admin.sort-link sort="created_at" :current-sort="$sort" :current-direction="$direction">Datum</x-admin.sort-link>
        <x-admin.sort-link sort="name" :current-sort="$sort" :current-direction="$direction">Naam</x-admin.sort-link>
        <x-admin.sort-link sort="size" :current-sort="$sort" :current-direction="$direction">Grootte</x-admin.sort-link>
    </div>

    {{-- Grid --}}
    @if($media->isEmpty())
        <div class="card card-body text-center text-muted py-5">
            <p class="mb-0">Geen media gevonden met deze filters.</p>
        </div>
    @else
        {{-- Reset de store bij elke page-load (defensief; nieuwe page-load betekent nieuwe HTTP-request, store is sowieso vers) --}}
        <div x-init="$store.mediaSelection.reset()" class="d-none"></div>

        {{-- "Selecteer alle zichtbare"-control --}}
        <div class="d-flex align-items-center justify-content-end mb-2 small text-muted">
            <label class="d-flex align-items-center gap-2" style="cursor: pointer;">
                <input
                    type="checkbox"
                    class="form-check-input m-0"
                    :checked="$store.mediaSelection.allVisibleSelected()"
                    @change="$event.target.checked ? $store.mediaSelection.selectAllVisible() : $store.mediaSelection.clear()"
                >
                <span>Selecteer alle zichtbare ({{ $media->count() }})</span>
            </label>
        </div>

        <div class="row g-3">
            @foreach($media as $item)
                <div class="col-6 col-md-3 col-xl-2 media-card-wrapper">
                    <div
                        class="card h-100 media-card"
                        style="position: relative;"
                        :class="{ 'media-card--selected': $store.mediaSelection.isSelected({{ $item->id }}) }"
                    >
                        {{-- Selectie-overlay linksboven --}}
                        <div
                            class="media-card__selection-overlay"
                            style="position: absolute; top: 0.5rem; left: 0.5rem; z-index: 2;"
                        >
                            <input
                                type="checkbox"
                                class="form-check-input"
                                data-media-id="{{ $item->id }}"
                                :checked="$store.mediaSelection.isSelected({{ $item->id }})"
                                @change="$store.mediaSelection.toggle({{ $item->id }})"
                            >
                        </div>

                        {{-- Delete-overlay rechtsboven (4.11.b) --}}
                        <x-admin.media-delete-overlay :media-id="$item->id" />

                        <div class="media-card__thumb ratio ratio-4x3 bg-light">
                            <img src="{{ $item->hasGeneratedConversion('thumb') ? $item->getUrl('thumb') : $item->getUrl() }}"
                                alt="{{ $item->getCustomProperty('alt', $item->name) }}"
                                class="w-100 h-100"
                                style="object-fit: cover;"
                                loading="lazy">
                        </div>
                        <div class="card-body p-2 small">
                            <div class="text-truncate fw-semibold" title="{{ $item->name }}">{{ $item->name }}</div>
                            <div class="text-muted text-truncate" title="{{ \App\Services\Media\MediaQueryBuilder::contextLabel($item) }}">
                                {{ \App\Services\Media\MediaQueryBuilder::contextLabel($item) }}
                            </div>
                            <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.75rem;">
                                <span>{{ $item->collection_name }}</span>
                                <span>{{ number_format($item->size / 1024, 0) }} KB</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $media->links() }}
        </div>

        {{-- Sticky-bottom action bar (F4-M8: C) --}}
        <div
            x-show="$store.mediaSelection.hasSelection()"
            x-cloak
            x-transition.opacity
            class="media-action-bar"
        >
            <div class="media-action-bar__inner">
                <span class="text-muted">
                    <span x-text="$store.mediaSelection.count()"></span>
                    <span x-text="$store.mediaSelection.count() === 1 ? 'item' : 'items'"></span> geselecteerd
                </span>
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="$store.mediaSelection.clear()">
                        Selectie wissen
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#mediaBulkDeleteModal">
                        <i class="bi bi-trash"></i> Verwijderen
                    </button>
                </div>
            </div>
        </div>

        {{-- Confirm-modal via @push('modals') (F4-N16) --}}
        @push('modals')
            <div
                class="modal fade"
                id="mediaBulkDeleteModal"
                tabindex="-1"
                aria-labelledby="mediaBulkDeleteModalLabel"
                aria-hidden="true"
                x-data
            >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title h5" id="mediaBulkDeleteModalLabel">
                                {{ __('Media verwijderen?') }}
                            </h2>
                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="{{ __('Sluiten') }}"
                            ></button>
                        </div>

                        <div class="modal-body">
                            <p class="mb-2">
                                <span x-text="$store.mediaSelection.count()"></span>
                                {{ __('media-items definitief verwijderen?') }}
                            </p>

                            <div class="alert alert-warning small mb-0" role="alert">
                                {{ __('Deze actie is onomkeerbaar. De foto\'s worden direct uit alle galerijen verwijderd.') }}
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                {{ __('Annuleren') }}
                            </button>
                            <button
                                type="button"
                                class="btn btn-danger"
                                @click="$store.mediaSelection.destroy()"
                            >
                                <i class="bi bi-trash"></i> {{ __('Definitief verwijderen') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endpush
    @endif
@endsection
