@extends('layouts.admin')

@section('title', __('Categorieën'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Categorieën') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="__('Categorieën')"
        :subtitle="__('Indeling voor blogposts (Verslag, Tips, Eten, Activiteit).')"
    >
        <x-slot:actions>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> {{ __('Nieuwe categorie') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filter / zoek --}}
    <x-admin.card>
        <form method="GET" action="{{ route('admin.categories.index') }}" class="d-flex gap-2 align-items-center">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="{{ __('Zoek op naam of beschrijving…') }}"
                class="form-control"
                style="max-width: 320px;"
            >
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-search"></i>
            </button>
            @if (request('search'))
                <a href="{{ route('admin.categories.index') }}" class="btn btn-link text-muted">
                    {{ __('Wissen') }}
                </a>
            @endif
        </form>
    </x-admin.card>

    {{-- Tabel --}}
    <x-admin.card>
        @if ($categories->isEmpty())
            <p class="text-muted m-0">
                @if (request('search'))
                    {{ __('Geen categorieën gevonden voor deze zoekopdracht.') }}
                @else
                    {{ __('Nog geen categorieën. Voeg er een toe om te beginnen.') }}
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
                                <x-admin.sort-link sort="name" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Naam') }}
                                </x-admin.sort-link>
                            </th>
                            <th>{{ __('Slug') }}</th>
                            <th>
                                <x-admin.sort-link sort="posts_count" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Posts') }}
                                </x-admin.sort-link>
                            </th>
                            <th class="text-end">{{ __('Acties') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td class="text-muted">{{ $category->order }}</td>
                                <td>
                                    <strong>{{ $category->name }}</strong>
                                    @if ($category->description)
                                        <div class="text-muted small">{{ Str::limit($category->description, 80) }}</div>
                                    @endif
                                </td>
                                <td><code class="text-muted">{{ $category->slug }}</code></td>
                                <td>
                                    <span class="badge text-bg-light">{{ $category->posts_count }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Bewerken') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <x-admin.delete-button :action="route('admin.categories.destroy', $category)" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($categories->hasPages())
                <div class="mt-3">
                    {{ $categories->links() }}
                </div>
            @endif
        @endif
    </x-admin.card>
@endsection
