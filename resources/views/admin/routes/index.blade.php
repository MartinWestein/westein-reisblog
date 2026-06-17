@extends('layouts.admin')

@section('title', __('Reisroutes'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Reisroutes') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        title="{{ __('Reisroutes') }}"
        subtitle="{{ __('Geordende reisroutes door bestemmingen heen. Een route is een serie locaties (waypoints) in volgorde.') }}"
    >
        <x-slot:actions>
            <a href="{{ route('admin.reisroutes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> {{ __('Nieuwe reisroute') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filter / zoek + bestemming + status --}}
    <x-admin.card>
        <form method="GET" action="{{ route('admin.reisroutes.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="{{ __('Zoek op naam…') }}"
                class="form-control"
                style="max-width: 280px;"
            >

            <select name="destination" class="form-select" style="max-width: 200px;" onchange="this.form.submit()">
                <option value="">{{ __('Alle bestemmingen') }}</option>
                @foreach ($destinations as $d)
                    <option value="{{ $d->slug }}" @selected($destinationFilter === $d->slug)>{{ $d->name }}</option>
                @endforeach
            </select>

            <select name="status" class="form-select" style="max-width: 180px;" onchange="this.form.submit()">
                <option value="all"       @selected($statusFilter === 'all')>{{ __('Alle statussen') }}</option>
                <option value="published" @selected($statusFilter === 'published')>{{ __('Gepubliceerd') }}</option>
                <option value="scheduled" @selected($statusFilter === 'scheduled')>{{ __('Gepland') }}</option>
                <option value="draft"     @selected($statusFilter === 'draft')>{{ __('Concept') }}</option>
            </select>

            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">

            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-search"></i>
            </button>

            @if (request('search') || $destinationFilter || $statusFilter !== 'all')
                <a href="{{ route('admin.reisroutes.index') }}" class="btn btn-link text-muted">
                    {{ __('Wissen') }}
                </a>
            @endif
        </form>
    </x-admin.card>

    {{-- Tabel --}}
    <x-admin.card>
        @if ($routes->isEmpty())
            <p class="text-muted m-0">
                @if (request('search') || $destinationFilter || $statusFilter !== 'all')
                    {{ __('Geen reisroutes gevonden voor deze filters.') }}
                @else
                    {{ __('Nog geen reisroutes. Maak er een aan om te beginnen.') }}
                @endif
            </p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Kaart') }}</th>
                            <th>
                                <x-admin.sort-link sort="name" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Naam') }}
                                </x-admin.sort-link>
                            </th>
                            <th>{{ __('Bestemming') }}</th>
                            <th>{{ __('Waypoints') }}</th>
                            <th>
                                <x-admin.sort-link sort="travel_date" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Reisdatum') }}
                                </x-admin.sort-link>
                            </th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Acties') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($routes as $route)
                            @php
                                if (! $route->is_published) {
                                    $statusLabel = __('Concept');
                                    $statusClass = 'text-bg-secondary';
                                } elseif ($route->published_at?->isFuture()) {
                                    $statusLabel = __('Gepland');
                                    $statusClass = 'text-bg-warning';
                                } else {
                                    $statusLabel = __('Gepubliceerd');
                                    $statusClass = 'text-bg-success';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <x-admin.route-thumb :waypoints="$route->waypoints" />
                                </td>
                                <td>
                                    <strong>{{ $route->name }}</strong>
                                    @if ($route->description)
                                        <div class="text-muted small">{{ Str::limit(strip_tags($route->description), 80) }}</div>
                                    @endif
                                </td>
                                <td>{{ $route->destination?->name ?? '—' }}</td>
                                <td class="text-muted">{{ $route->waypoints_count }}</td>
                                <td class="text-muted small">
                                    {{ $route->travel_date?->format('d-m-Y') ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.reisroutes.edit', $route) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Bewerken') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <x-admin.delete-button :action="route('admin.reisroutes.destroy', $route)" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($routes->hasPages())
                <div class="mt-3">
                    {{ $routes->links() }}
                </div>
            @endif
        @endif
    </x-admin.card>
@endsection
