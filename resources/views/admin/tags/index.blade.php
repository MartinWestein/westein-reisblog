@extends('layouts.admin')

@section('title', __('Tags'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Tags') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="__('Tags')"
        :subtitle="__('Vrije labels voor posts. Tag-namen worden automatisch lowercase.')"
    >
        <x-slot:actions>
            <a href="{{ route('admin.tags.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> {{ __('Nieuwe tag') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filter / zoek --}}
    <x-admin.card>
        <form method="GET" action="{{ route('admin.tags.index') }}" class="d-flex gap-2 align-items-center">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="{{ __('Zoek op naam…') }}"
                class="form-control"
                style="max-width: 320px;"
            >
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-search"></i>
            </button>
            @if (request('search'))
                <a href="{{ route('admin.tags.index') }}" class="btn btn-link text-muted">
                    {{ __('Wissen') }}
                </a>
            @endif
        </form>
    </x-admin.card>

    {{-- Tabel --}}
    <x-admin.card>
        @if ($tags->isEmpty())
            <p class="text-muted m-0">
                @if (request('search'))
                    {{ __('Geen tags gevonden voor deze zoekopdracht.') }}
                @else
                    {{ __('Nog geen tags. Voeg er een toe om te beginnen.') }}
                @endif
            </p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>
                                <x-admin.sort-link sort="name" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Naam') }}
                                </x-admin.sort-link>
                            </th>
                            <th>{{ __('Slug') }}</th>
                            <th>
                                <x-admin.sort-link sort="posts_count" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Gebruikt in') }}
                                </x-admin.sort-link>
                            </th>
                            <th class="text-end">{{ __('Acties') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tags as $tag)
                            <tr>
                                <td><strong>{{ $tag->name }}</strong></td>
                                <td><code class="text-muted">{{ $tag->slug }}</code></td>
                                <td>
                                    @if ($tag->posts_count > 0)
                                        <span class="badge text-bg-light">{{ $tag->posts_count }} {{ trans_choice('post|posts', $tag->posts_count) }}</span>
                                    @else
                                        <span class="text-muted small">{{ __('niet in gebruik') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.tags.edit', $tag) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Bewerken') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <x-admin.delete-button :action="route('admin.tags.destroy', $tag)" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($tags->hasPages())
                <div class="mt-3">
                    {{ $tags->links() }}
                </div>
            @endif
        @endif
    </x-admin.card>
@endsection
