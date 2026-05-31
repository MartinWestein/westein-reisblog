@extends('layouts.admin')

@section('title', "Pagina's")

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">Pagina's</span>
@endsection

@section('content')
    <x-admin.page-header
        title="Pagina's"
        subtitle="Statische pagina's zoals Over ons, Privacy en Contact."
    >
        <x-slot:actions>
            <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> {{ __('Nieuwe pagina') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filter / zoek + statusfilter --}}
    <x-admin.card>
        <form method="GET" action="{{ route('admin.pages.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="{{ __('Zoek op titel of slug…') }}"
                class="form-control"
                style="max-width: 280px;"
            >

            <select name="status" class="form-select" style="max-width: 180px;" onchange="this.form.submit()">
                <option value="all"       @selected($status === 'all')>{{ __('Alle statussen') }}</option>
                <option value="published" @selected($status === 'published')>{{ __('Gepubliceerd') }}</option>
                <option value="scheduled" @selected($status === 'scheduled')>{{ __('Gepland') }}</option>
                <option value="draft"     @selected($status === 'draft')>{{ __('Concept') }}</option>
            </select>

            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">

            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-search"></i>
            </button>

            @if (request('search') || $status !== 'all')
                <a href="{{ route('admin.pages.index') }}" class="btn btn-link text-muted">
                    {{ __('Wissen') }}
                </a>
            @endif
        </form>
    </x-admin.card>

    {{-- Tabel --}}
    <x-admin.card>
        @if ($pages->isEmpty())
            <p class="text-muted m-0">
                @if (request('search') || $status !== 'all')
                    {{ __('Geen pagina\'s gevonden voor deze filters.') }}
                @else
                    {{ __('Nog geen pagina\'s. Maak er een aan om te beginnen.') }}
                @endif
            </p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>
                                <x-admin.sort-link sort="order" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Volgorde') }}
                                </x-admin.sort-link>
                            </th>
                            <th>
                                <x-admin.sort-link sort="title" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Titel') }}
                                </x-admin.sort-link>
                            </th>
                            <th>{{ __('Slug') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>
                                <x-admin.sort-link sort="published_at" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Publicatiedatum') }}
                                </x-admin.sort-link>
                            </th>
                            <th class="text-end">{{ __('Acties') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pages as $page)
                            @php
                                if (is_null($page->published_at)) {
                                    $statusLabel = __('Concept');
                                    $statusClass = 'text-bg-secondary';
                                } elseif ($page->published_at->isFuture()) {
                                    $statusLabel = __('Gepland');
                                    $statusClass = 'text-bg-warning';
                                } else {
                                    $statusLabel = __('Gepubliceerd');
                                    $statusClass = 'text-bg-success';
                                }
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $page->order }}</td>
                                <td>
                                    <strong>{{ $page->title }}</strong>
                                    @if ($page->excerpt)
                                        <div class="text-muted small">{{ Str::limit($page->excerpt, 80) }}</div>
                                    @endif
                                </td>
                                <td><code class="text-muted">{{ $page->slug }}</code></td>
                                <td>
                                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td class="text-muted small">
                                    {{ $page->published_at?->format('d-m-Y H:i') ?? '—' }}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Bewerken') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <x-admin.delete-button :action="route('admin.pages.destroy', $page)" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($pages->hasPages())
                <div class="mt-3">
                    {{ $pages->links() }}
                </div>
            @endif
        @endif
    </x-admin.card>
@endsection
