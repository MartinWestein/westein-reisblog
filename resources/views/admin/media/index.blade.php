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
        <div class="row g-3">
            @foreach($media as $item)
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="card h-100 media-card">
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
    @endif
@endsection
