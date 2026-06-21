@extends('layouts.admin')

@section('title', __('Abonnees'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Abonnees') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="__('Abonnees')"
        :subtitle="__('Nieuwsbrief-abonnees beheren, importeren en exporteren.')">
        <x-slot:actions>
            <a href="{{ route('admin.subscribers.export', request()->only(['status', 'search'])) }}"
               class="btn btn-outline-secondary">
                <i class="bi bi-download me-1"></i> {{ __('Exporteren') }}
            </a>
            <a href="{{ route('admin.subscribers.create') }}#import"
               class="btn btn-outline-secondary">
                <i class="bi bi-upload me-1"></i> {{ __('Importeren') }}
            </a>
            <a href="{{ route('admin.subscribers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> {{ __('Nieuw') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card>
        <form method="GET" action="{{ route('admin.subscribers.index') }}"
              class="d-flex gap-2 align-items-center flex-wrap mb-3">
            <div class="input-group" style="max-width: 22rem;">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input type="search" name="search" value="{{ $search }}"
                       class="form-control"
                       placeholder="{{ __('Zoek op naam of e-mailadres…') }}">
            </div>

            <select name="status" class="form-select" style="max-width: 18rem;"
                    onchange="this.form.submit()">
                <option value="all" @selected($status === 'all')>
                    {{ __('Alle statussen') }} ({{ $counts['all'] }})
                </option>
                <option value="pending" @selected($status === 'pending')>
                    {{ __('Wachten op bevestiging') }} ({{ $counts['pending'] }})
                </option>
                <option value="active" @selected($status === 'active')>
                    {{ __('Actief') }} ({{ $counts['active'] }})
                </option>
                <option value="unsubscribed" @selected($status === 'unsubscribed')>
                    {{ __('Uitgeschreven') }} ({{ $counts['unsubscribed'] }})
                </option>
            </select>

            <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>

            @if ($search !== '' || $status !== 'all')
                <a href="{{ route('admin.subscribers.index') }}"
                   class="btn btn-link text-muted">{{ __('Reset') }}</a>
            @endif
        </form>

        @if ($counts['pending'] > 0)
            <div class="alert alert-info d-flex justify-content-between align-items-center mb-3"
                x-data="{ confirming: false }">
                <div>
                    <i class="bi bi-envelope-arrow-up me-2"></i>
                    {{ trans_choice(
                        '{1} Er is :count abonnee die wacht op bevestiging.|[2,*] Er zijn :count abonnees die wachten op bevestiging.',
                        $counts['pending'],
                        ['count' => $counts['pending']]
                    ) }}
                </div>

                <div>
                    <template x-if="! confirming">
                        <button type="button" class="btn btn-sm btn-primary" @click="confirming = true">
                            {{ __('Stuur naar allen') }}
                        </button>
                    </template>

                    <template x-if="confirming">
                        <form method="POST" action="{{ route('admin.subscribers.send-bulk-confirmations') }}"
                            class="d-inline-flex gap-2">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">
                                {{ __('Bevestig: stuur :count mails', ['count' => $counts['pending']]) }}
                            </button>
                            <button type="button" class="btn btn-sm btn-link" @click="confirming = false">
                                {{ __('Annuleren') }}
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        @endif
        
        @if ($subscribers->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-envelope-paper" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">{{ __('Geen abonnees gevonden.') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 2.5rem;"></th>
                            <th>
                                <x-admin.sort-link sort="email" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('E-mailadres') }}
                                </x-admin.sort-link>
                            </th>
                            <th>
                                <x-admin.sort-link sort="name" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Naam') }}
                                </x-admin.sort-link>
                            </th>
                            <th>{{ __('Status') }}</th>
                            <th>
                                <x-admin.sort-link sort="created_at" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Aangemeld') }}
                                </x-admin.sort-link>
                            </th>
                            <th class="text-end">{{ __('Acties') }}</th>
                        </tr>
                    </thead>
                    <tbody x-data="{ expanded: null }">
                        @foreach ($subscribers as $subscriber)
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-link btn-sm p-0"
                                            @click="expanded = expanded === {{ $subscriber->id }} ? null : {{ $subscriber->id }}"
                                            :aria-expanded="expanded === {{ $subscriber->id }}"
                                            aria-label="{{ __('Toon details') }}">
                                        <i class="bi"
                                           :class="expanded === {{ $subscriber->id }} ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                                    </button>
                                </td>
                                <td>
                                    <a href="{{ route('admin.subscribers.edit', $subscriber) }}"
                                       class="text-decoration-none">{{ $subscriber->email }}</a>
                                </td>
                                <td>{{ $subscriber->name ?: '—' }}</td>
                                <td>
                                    <x-admin.subscriber-status-badge :status="$subscriber->status()" />
                                </td>
                                <td class="text-muted small">
                                    {{ $subscriber->created_at->isoFormat('D MMM YYYY') }}
                                </td>
                                <td class="text-end">
                                    @if ($subscriber->status() === \App\Models\Subscriber::STATUS_PENDING)
                                        <form method="POST"
                                              action="{{ route('admin.subscribers.send-confirmation', $subscriber) }}"
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                    title="{{ __('Verstuur bevestigingsmail') }}">
                                                <i class="bi bi-envelope-arrow-up"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.subscribers.edit', $subscriber) }}"
                                       class="btn btn-sm btn-outline-secondary"
                                       title="{{ __('Bewerken') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <x-admin.delete-button
                                        :action="route('admin.subscribers.destroy', $subscriber)"
                                        :label="__('Verwijderen')" />
                                </td>
                            </tr>

                            <tr x-show="expanded === {{ $subscriber->id }}" x-cloak>
                                <td colspan="6" class="bg-light">
                                    <dl class="row mb-0 small">
                                        <dt class="col-sm-3">{{ __('ID') }}</dt>
                                        <dd class="col-sm-9 font-monospace">#{{ $subscriber->id }}</dd>

                                        <dt class="col-sm-3">{{ __('Bevestigd op') }}</dt>
                                        <dd class="col-sm-9">
                                            {{ $subscriber->confirmed_at?->isoFormat('D MMM YYYY HH:mm') ?? '—' }}
                                        </dd>

                                        <dt class="col-sm-3">{{ __('Uitgeschreven op') }}</dt>
                                        <dd class="col-sm-9">
                                            {{ $subscriber->unsubscribed_at?->isoFormat('D MMM YYYY HH:mm') ?? '—' }}
                                        </dd>

                                        <dt class="col-sm-3">{{ __('Confirmation-token') }}</dt>
                                        <dd class="col-sm-9 font-monospace text-muted text-break">
                                            {{ $subscriber->confirmation_token ?: '—' }}
                                        </dd>

                                        <dt class="col-sm-3">{{ __('Unsubscribe-token') }}</dt>
                                        <dd class="col-sm-9 font-monospace text-muted text-break">
                                            {{ $subscriber->unsubscribe_token }}
                                        </dd>
                                    </dl>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $subscribers->links() }}
            </div>
        @endif
    </x-admin.card>
@endsection
