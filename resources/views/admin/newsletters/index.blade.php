@extends('layouts.admin')

@section('title', __('Nieuwsbrieven'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Nieuwsbrieven') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="__('Nieuwsbrieven')"
        :subtitle="__('Stel nieuwsbrieven samen en verstuur ze naar actieve abonnees.')">
        <x-slot:actions>
            <a href="{{ route('admin.newsletters.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> {{ __('Nieuw') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card>
        <form method="GET" action="{{ route('admin.newsletters.index') }}"
              class="d-flex gap-2 align-items-center flex-wrap mb-3">
            <div class="input-group" style="max-width: 22rem;">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input type="search" name="search" value="{{ $search }}"
                       class="form-control"
                       placeholder="{{ __('Zoek op onderwerp…') }}">
            </div>

            <select name="status" class="form-select" style="max-width: 18rem;"
                    onchange="this.form.submit()">
                <option value="all" @selected($status === 'all')>
                    {{ __('Alle statussen') }} ({{ $counts['all'] }})
                </option>
                <option value="draft" @selected($status === 'draft')>
                    {{ __('Concept') }} ({{ $counts['draft'] }})
                </option>
                <option value="sending" @selected($status === 'sending')>
                    {{ __('Wordt verzonden') }} ({{ $counts['sending'] }})
                </option>
                <option value="sent" @selected($status === 'sent')>
                    {{ __('Verzonden') }} ({{ $counts['sent'] }})
                </option>
            </select>

            <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>

            @if ($search !== '' || $status !== 'all')
                <a href="{{ route('admin.newsletters.index') }}"
                   class="btn btn-link text-muted">{{ __('Reset') }}</a>
            @endif
        </form>

        @if ($newsletters->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-megaphone" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">{{ __('Geen nieuwsbrieven gevonden.') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>
                                <x-admin.sort-link sort="subject" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Onderwerp') }}
                                </x-admin.sort-link>
                            </th>
                            <th>{{ __('Auteur') }}</th>
                            <th>
                                <x-admin.sort-link sort="status" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Status') }}
                                </x-admin.sort-link>
                            </th>
                            <th class="text-end">{{ __('Ontvangers') }}</th>
                            <th>
                                <x-admin.sort-link sort="sent_at" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Verzonden op') }}
                                </x-admin.sort-link>
                            </th>
                            <th>
                                <x-admin.sort-link sort="created_at" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Aangemaakt') }}
                                </x-admin.sort-link>
                                <th class="text-end">{{ __('Acties') }}</th>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($newsletters as $newsletter)
                            <tr>
                                <td>
                                    <span class="text-decoration-none">{{ $newsletter->subject }}</span>
                                </td>
                                <td>{{ $newsletter->author?->name ?? '—' }}</td>
                                <td>
                                    @php
                                        $badgeClass = match ($newsletter->status) {
                                            'draft' => 'bg-secondary',
                                            'sending' => 'bg-info text-dark',
                                            'sent' => 'bg-success',
                                            default => 'bg-light text-dark',
                                        };
                                        $badgeLabel = match ($newsletter->status) {
                                            'draft' => __('Concept'),
                                            'sending' => __('Wordt verzonden'),
                                            'sent' => __('Verzonden'),
                                            default => $newsletter->status,
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                </td>
                                <td class="text-end">{{ $newsletter->recipients_count }}</td>
                                <td class="text-muted small">
                                    {{ $newsletter->sent_at?->isoFormat('D MMM YYYY HH:mm') ?? '—' }}
                                </td>
                                <td class="text-muted small">
                                    {{ $newsletter->created_at->isoFormat('D MMM YYYY') }}
                                </td>
                                <td class="text-end">
                                    @if ($newsletter->isEditable())
                                        <a href="{{ route('admin.newsletters.edit', $newsletter) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="{{ __('Bewerken') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <x-admin.delete-button
                                            :action="route('admin.newsletters.destroy', $newsletter)"
                                            :label="__('Verwijderen')" />
                                    @else
                                        <span class="text-muted small">{{ __('Read-only') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $newsletters->links() }}
            </div>
        @endif
    </x-admin.card>
@endsection
