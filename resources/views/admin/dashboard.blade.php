@extends('layouts.admin')

@section('title', __('Dashboard'))

@section('content')
    <x-admin.page-header
        :title="__('Welkom terug, ') . auth()->user()->name"
        :subtitle="__('Overzicht van het Westein Reis Blog beheer.')"
    />

    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <x-admin.card>
                <div class="text-muted small text-uppercase" style="letter-spacing: 0.08em;">{{ __('Posts') }}</div>
                <div style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; margin-top: 0.5rem;">—</div>
                <div class="text-muted small">{{ __('Wordt gevuld in Stap 4.2') }}</div>
            </x-admin.card>
        </div>
        <div class="col-md-6 col-lg-3">
            <x-admin.card>
                <div class="text-muted small text-uppercase" style="letter-spacing: 0.08em;">{{ __('Te modereren') }}</div>
                <div style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; margin-top: 0.5rem;">—</div>
                <div class="text-muted small">{{ __('Wordt gevuld in Stap 4.2') }}</div>
            </x-admin.card>
        </div>
        <div class="col-md-6 col-lg-3">
            <x-admin.card>
                <div class="text-muted small text-uppercase" style="letter-spacing: 0.08em;">{{ __('Abonnees') }}</div>
                <div style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; margin-top: 0.5rem;">—</div>
                <div class="text-muted small">{{ __('Wordt gevuld in Stap 4.2') }}</div>
            </x-admin.card>
        </div>
        <div class="col-md-6 col-lg-3">
            <x-admin.card>
                <div class="text-muted small text-uppercase" style="letter-spacing: 0.08em;">{{ __('Geplande brieven') }}</div>
                <div style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; margin-top: 0.5rem;">—</div>
                <div class="text-muted small">{{ __('Wordt gevuld in Stap 4.2') }}</div>
            </x-admin.card>
        </div>
    </div>

    <x-admin.card :title="__('Volgende stappen')">
        <p class="mb-0 text-muted">
            {{ __('Layout-fundering staat. Stap 4.2 vult deze cards met echte data, Stap 4.3 voegt de eerste CRUD-modules toe.') }}
        </p>
    </x-admin.card>
@endsection
