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

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    {{ __('Annuleren') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ __('Opslaan') }}
                </button>
            </div>
        </x-admin.card>
    </form>
@endsection
