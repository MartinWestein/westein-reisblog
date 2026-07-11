@extends('layouts.admin')

@section('title', __('Gebruikers'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Gebruikers') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="__('Gebruikers')"
        :subtitle="__('Beheer accounts, rollen en toegang.')">
        <x-slot:actions>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> {{ __('Nieuw') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card>
        <form method="GET" action="{{ route('admin.users.index') }}"
              class="d-flex gap-2 align-items-center flex-wrap mb-3">
            <div class="input-group" style="max-width: 22rem;">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input type="search" name="search" value="{{ $search }}"
                       class="form-control"
                       placeholder="{{ __('Zoek op naam of e-mailadres...') }}">
            </div>

            <select name="role" class="form-select" style="max-width: 12rem;"
                    onchange="this.form.submit()">
                <option value="all" @selected($roleFilter === 'all')>
                    {{ __('Alle rollen') }}
                </option>
                @foreach ($roleNames as $name)
                    <option value="{{ $name }}" @selected($roleFilter === $name)>
                        {{ ucfirst($name) }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="form-select" style="max-width: 12rem;"
                    onchange="this.form.submit()">
                <option value="all" @selected($status === 'all')>
                    {{ __('Alle statussen') }}
                </option>
                <option value="active" @selected($status === 'active')>
                    {{ __('Actief') }}
                </option>
                <option value="deactivated" @selected($status === 'deactivated')>
                    {{ __('Gedeactiveerd') }}
                </option>
            </select>

            <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>

            @if ($search !== '' || $roleFilter !== 'all' || $status !== 'all')
                <a href="{{ route('admin.users.index') }}"
                   class="btn btn-link text-muted">{{ __('Reset') }}</a>
            @endif
        </form>

        @if ($users->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-person-x" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">{{ __('Geen gebruikers gevonden.') }}</p>
            </div>
        @else
            {{-- Reset de store bij elke page-load (analoog aan trashSelection) --}}
            <div x-init="$store.userSelection.reset()" class="d-none"></div>

            {{-- "Selecteer alle zichtbare"-control --}}
            <div class="mb-2">
                <label class="d-inline-flex align-items-center gap-2 small text-muted">
                    <input
                        type="checkbox"
                        class="form-check-input m-0"
                        :checked="$store.userSelection.allVisibleSelected()"
                        @change="$event.target.checked ? $store.userSelection.selectAllVisible() : $store.userSelection.clear()"
                    >
                    <span>{{ __('Selecteer alle zichtbare') }} ({{ $users->count() }})</span>
                </label>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>
                                <x-admin.sort-link sort="name" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Naam') }}
                                </x-admin.sort-link>
                            </th>
                            <th>
                                <x-admin.sort-link sort="email" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('E-mailadres') }}
                                </x-admin.sort-link>
                            </th>
                            <th>{{ __('Rollen') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Acties') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr data-user-id="{{ $user->id }}">
                                <td>
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        :checked="$store.userSelection.isSelected({{ $user->id }})"
                                        @change="$store.userSelection.toggle({{ $user->id }})"
                                    >
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <x-admin.avatar-initials :subject="$user" />
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                           class="text-decoration-none fw-medium">
                                            {{ $user->name }}
                                        </a>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $user->email }}</td>
                                <td>
                                    @forelse ($user->roles as $role)
                                        <span class="badge bg-light text-dark border me-1">{{ ucfirst($role->name) }}</span>
                                    @empty
                                        <span class="text-muted small">{{ __('Geen rollen') }}</span>
                                    @endforelse
                                </td>
                                <td>
                                    <x-admin.user-status-badge :user="$user" />
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="btn btn-sm btn-outline-secondary"
                                       title="{{ __('Bewerken') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links() }}
            </div>

            {{-- Sticky-bottom action bar --}}
            <div
                x-show="$store.userSelection.hasSelection()"
                x-cloak
                x-transition.opacity
                class="media-action-bar"
            >
                <div class="media-action-bar__inner">
                    <span class="text-muted">
                        <span x-text="$store.userSelection.count()"></span>
                        <span x-text="$store.userSelection.count() === 1 ? '{{ __('gebruiker') }}' : '{{ __('gebruikers') }}'"></span> {{ __('geselecteerd') }}
                    </span>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                @click="$store.userSelection.clear()">
                            {{ __('Selectie wissen') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-primary"
                                data-bs-toggle="modal" data-bs-target="#usersBulkReactivateModal">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> {{ __('Reactiveren') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-danger"
                                data-bs-toggle="modal" data-bs-target="#usersBulkDeactivateModal">
                            <i class="bi bi-person-slash me-1"></i> {{ __('Deactiveren') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Hidden forms voor bulk-submits --}}
            <form
                id="users-bulk-deactivate-form"
                method="POST"
                action="{{ route('admin.users.bulk-deactivate') }}"
                class="d-none"
            >
                @csrf
                <input type="hidden" name="ids" value="">
            </form>

            <form
                id="users-bulk-reactivate-form"
                method="POST"
                action="{{ route('admin.users.bulk-reactivate') }}"
                class="d-none"
            >
                @csrf
                <input type="hidden" name="ids" value="">
            </form>
        @endif
    </x-admin.card>
@endsection

@push('modals')
    <div class="modal fade" id="usersBulkDeactivateModal" tabindex="-1" aria-hidden="true" x-data>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5">{{ __('Gebruikers deactiveren?') }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Sluiten') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">
                        <span x-text="$store.userSelection.count()"></span>
                        {{ __('gebruikers deactiveren?') }}
                    </p>
                    <p class="small text-muted mb-0">
                        {{ __('Reeds gedeactiveerde gebruikers worden overgeslagen. Bestaande content blijft zichtbaar.') }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('Annuleren') }}
                    </button>
                    <button type="button" class="btn btn-danger"
                            @click="$store.userSelection.destroyDeactivate()">
                        <i class="bi bi-person-slash me-1"></i> {{ __('Deactiveren') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="usersBulkReactivateModal" tabindex="-1" aria-hidden="true" x-data>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5">{{ __('Gebruikers reactiveren?') }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Sluiten') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">
                        <span x-text="$store.userSelection.count()"></span>
                        {{ __('gebruikers reactiveren?') }}
                    </p>
                    <p class="small text-muted mb-0">
                        {{ __('Reeds actieve gebruikers worden overgeslagen.') }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('Annuleren') }}
                    </button>
                    <button type="button" class="btn btn-primary"
                            @click="$store.userSelection.destroyReactivate()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> {{ __('Reactiveren') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush
