@extends('layouts.admin')

@section('title', __('Gebruiker bewerken'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.users.index') }}">{{ __('Gebruikers') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ $user->name }}</span>
@endsection

@section('content')
    <x-admin.page-header :title="$user->name">
        <x-slot:subtitle>
            <span class="text-muted">{{ $user->email }}</span>
            @if ($user->deactivated_at)
                <span class="ms-2">
                    <x-admin.user-status-badge :user="$user" />
                </span>
            @endif
        </x-slot:subtitle>
    </x-admin.page-header>

    @if ($user->deactivated_at)
        <div class="alert alert-secondary d-flex justify-content-between align-items-start mb-4">
            <div>
                <strong>{{ __('Deze gebruiker is gedeactiveerd.') }}</strong>
                <div class="small text-muted mt-1">
                    {{ __('Gedeactiveerd op :date', ['date' => $user->deactivated_at->isoFormat('D MMM YYYY HH:mm')]) }}
                </div>
                @if ($user->deactivation_reason)
                    <div class="mt-2">
                        <div class="small text-muted">{{ __('Reden:') }}</div>
                        <div>{{ $user->deactivation_reason }}</div>
                    </div>
                @endif
            </div>
            <form method="POST" action="{{ route('admin.users.reactivate', $user) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> {{ __('Reactiveren') }}
                </button>
            </form>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PATCH')

        <x-admin.card>
            <div class="mb-3">
                <label for="name" class="form-label">{{ __('Naam') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                       class="form-control @error('name') is-invalid @enderror"
                       required maxlength="100">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">{{ __('E-mailadres') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                       class="form-control @error('email') is-invalid @enderror"
                       required maxlength="255">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                    {{ __('Bij wijziging krijgt de gebruiker een nieuwe activatie-mail en moet opnieuw een wachtwoord instellen.') }}
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('Rollen') }}</label>
                @php
                    $selectedRoles = old('roles', $currentRoles);
                @endphp
                @foreach ($roleNames as $name)
                    <div class="form-check">
                        <input type="checkbox" name="roles[]" value="{{ $name }}"
                               id="role_{{ $name }}"
                               class="form-check-input"
                               @checked(in_array($name, $selectedRoles, true))>
                        <label class="form-check-label" for="role_{{ $name }}">
                            {{ ucfirst($name) }}
                        </label>
                    </div>
                @endforeach
                @error('roles')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                @error('roles.*')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between gap-2">
                <div>
                    @if (! $user->deactivated_at && $user->id !== auth()->id())
                        <button type="button" class="btn btn-outline-danger"
                                data-bs-toggle="modal" data-bs-target="#deactivateUserModal">
                            <i class="bi bi-person-slash me-1"></i> {{ __('Deactiveren') }}
                        </button>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        {{ __('Annuleren') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        {{ __('Opslaan') }}
                    </button>
                </div>
            </div>
        </x-admin.card>
    </form>
@endsection

@if (! $user->deactivated_at && $user->id !== auth()->id())
    @push('modals')
        <div class="modal fade" id="deactivateUserModal" tabindex="-1" aria-hidden="true" x-data>
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Gebruiker deactiveren') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Sluiten') }}"></button>
                    </div>
                    <div class="modal-body">
                        <p>
                            {{ __(':name kan na deactivatie niet meer inloggen. Bestaande content (posts, reacties) blijft zichtbaar.', ['name' => $user->name]) }}
                        </p>
                        <div class="mb-0">
                            <label for="reason" class="form-label">{{ __('Reden (optioneel)') }}</label>
                            <textarea name="reason" id="reason" rows="3" maxlength="500"
                                      class="form-control @error('reason') is-invalid @enderror"
                                      placeholder="{{ __('Bijvoorbeeld: op eigen verzoek, spam-account, ...') }}">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('Annuleren') }}
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-person-slash me-1"></i> {{ __('Deactiveren') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endpush
@endif
