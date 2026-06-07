@extends('layouts.admin')

@section('title', __('Bestemming bewerken'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.destinations.index') }}">{{ __('Bestemmingen') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ $destination->name }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="$destination->name"
        :subtitle="__('Bestemming bewerken')"
    />

    @include('admin.destinations._form', [
        'destination' => $destination,
        'action' => route('admin.destinations.update', $destination),
        'method' => 'PUT',
    ])
    
    <div class="mt-4">
        <a href="{{ route('admin.destinations.locations.index', $destination) }}" class="btn btn-outline-primary">
            <i class="bi bi-pin-map"></i>
            {{ __('Locaties beheren') }}
        </a>
    </div>

    <section class="admin-form-section mt-4">
        <header class="admin-form-section__header">
            <h3 class="admin-form-section__title">{{ __('Galerij') }}</h3>
            <p class="admin-form-section__description">
                {{ __('Voeg foto\'s toe, sleep om te ordenen. Wijzigingen worden direct opgeslagen.') }}
            </p>
        </header>
        <div class="admin-form-section__body">
            <x-admin.gallery-upload :model="$destination" name="gallery" :max-mb="16" />
        </div>
    </section>
@endsection
