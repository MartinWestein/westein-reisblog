@extends('layouts.admin')

@section('title', 'Posts')

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">Posts</span>
@endsection

@section('content')
    <x-admin.page-header
        title="Posts"
        subtitle="Reisverslagen, reistips en artikelen."
    >
        <x-slot:actions>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> {{ __('Nieuwe post') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filters --}}
    <x-admin.card>
        <form method="GET" action="{{ route('admin.posts.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
            <input
                type="text" name="search" value="{{ request('search') }}"
                placeholder="{{ __('Zoek op titel of slug…') }}"
                class="form-control" style="max-width: 240px;"
            >

            <select name="status" class="form-select" style="max-width: 160px;" onchange="this.form.submit()">
                <option value="all"       @selected($status === 'all')>{{ __('Alle statussen') }}</option>
                <option value="published" @selected($status === 'published')>{{ __('Gepubliceerd') }}</option>
                <option value="scheduled" @selected($status === 'scheduled')>{{ __('Gepland') }}</option>
                <option value="draft"     @selected($status === 'draft')>{{ __('Concept') }}</option>
                <option value="archived"  @selected($status === 'archived')>{{ __('Gearchiveerd') }}</option>
            </select>

            <select name="author" class="form-select" style="max-width: 160px;" onchange="this.form.submit()">
                <option value="">{{ __('Alle auteurs') }}</option>
                @foreach ($authors as $a)
                    <option value="{{ $a->id }}" @selected((int) $author === $a->id)>{{ $a->name }}</option>
                @endforeach
            </select>

            <select name="destination" class="form-select" style="max-width: 180px;" onchange="this.form.submit()">
                <option value="">{{ __('Alle bestemmingen') }}</option>
                @foreach ($destinations as $d)
                    <option value="{{ $d->id }}" @selected((int) $destination === $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>

            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">

            <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>

            @if (request('search') || $status !== 'all' || $author || $destination)
                <a href="{{ route('admin.posts.index') }}" class="btn btn-link text-muted">{{ __('Wissen') }}</a>
            @endif
        </form>
    </x-admin.card>

    {{-- Tabel --}}
    <x-admin.card>
        @if ($posts->isEmpty())
            <p class="text-muted m-0">
                @if (request('search') || $status !== 'all' || $author || $destination)
                    {{ __('Geen posts gevonden voor deze filters.') }}
                @else
                    {{ __('Nog geen posts. Maak er een aan om te beginnen.') }}
                @endif
            </p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>
                                <x-admin.sort-link sort="title" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Titel') }}
                                </x-admin.sort-link>
                            </th>
                            <th>{{ __('Auteur') }}</th>
                            <th>{{ __('Categorieën') }}</th>
                            <th>
                                <x-admin.sort-link sort="status" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Status') }}
                                </x-admin.sort-link>
                            </th>
                            <th>
                                <x-admin.sort-link sort="published_at" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Gepubliceerd') }}
                                </x-admin.sort-link>
                            </th>
                            <th class="text-end">{{ __('Acties') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $post)
                            @php
                                $badge = match ($post->status) {
                                    'published' => ['Gepubliceerd', 'text-bg-success'],
                                    'scheduled' => ['Gepland', 'text-bg-warning'],
                                    'archived'  => ['Gearchiveerd', 'text-bg-dark'],
                                    default     => ['Concept', 'text-bg-secondary'],
                                };
                                $thumb = $post->getFirstMediaUrl('featured', 'thumb');
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($thumb)
                                            <img src="{{ $thumb }}" alt="" width="40" height="40"
                                                 style="object-fit: cover; border-radius: 4px; flex-shrink: 0;">
                                        @else
                                            <span class="d-inline-flex align-items-center justify-content-center text-muted"
                                                  style="width:40px;height:40px;background:#F0EEE8;border-radius:4px;flex-shrink:0;">
                                                <i class="bi bi-image"></i>
                                            </span>
                                        @endif

                                        <div>
                                            <strong>{{ $post->title }}</strong>
                                            @if ($post->is_featured)
                                                <span class="badge bg-warning text-dark ms-2">
                                                    <i class="bi bi-star-fill" aria-hidden="true"></i>
                                                    {{ __('Uitgelicht') }}
                                                </span>
                                            @endif
                                            <div><code class="text-muted small">{{ $post->slug }}</code></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted small">{{ $post->author?->name ?? '—' }}</td>
                                <td>
                                    @forelse ($post->categories as $cat)
                                        <span class="badge text-bg-light">{{ $cat->name }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>
                                <td><span class="badge {{ $badge[1] }}">{{ $badge[0] }}</span></td>
                                <td class="text-muted small">{{ $post->published_at?->format('d-m-Y H:i') ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Bewerken') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <x-admin.delete-button :action="route('admin.posts.destroy', $post)" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($posts->hasPages())
                <div class="mt-3">{{ $posts->links() }}</div>
            @endif
        @endif
    </x-admin.card>
@endsection
