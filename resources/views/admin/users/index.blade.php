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
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
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
                            <tr>
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
        @endif
    </x-admin.card>
@endsection
