@extends('layouts.admin')

@section('title', __('Locatie bewerken'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.destinations.index') }}">{{ __('Bestemmingen') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.destinations.edit', $destination) }}">{{ $destination->name }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.destinations.locations.index', $destination) }}">{{ __('Locaties') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ $location->name }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="$location->name"
        :subtitle="__('Locatie bewerken')"
    />

    @include('admin.locations._form', [
        'destination' => $destination,
        'location' => $location,
        'action' => route('admin.destinations.locations.update', [$destination, $location]),
        'method' => 'PUT',
    ])

    <section class="admin-form-section mt-4">
        <header class="admin-form-section__header">
            <h3 class="admin-form-section__title">{{ __('Galerij') }}</h3>
            <p class="admin-form-section__description">
                {{ __('Voeg foto\'s toe, sleep om te ordenen. Wijzigingen worden direct opgeslagen.') }}
            </p>
        </header>
        <div class="admin-form-section__body">
            <x-admin.gallery-upload :model="$location" name="gallery" :max-mb="16" />
        </div>
    </section>
@endsection
