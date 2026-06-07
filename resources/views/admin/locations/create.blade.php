@extends('layouts.admin')

@section('title', __('Nieuwe locatie'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.destinations.index') }}">{{ __('Bestemmingen') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.destinations.edit', $destination) }}">{{ $destination->name }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.destinations.locations.index', $destination) }}">{{ __('Locaties') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Nieuw') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="__('Nieuwe locatie')"
        :subtitle="__('Voeg een plek toe binnen :name.', ['name' => $destination->name])"
    />

    @include('admin.locations._form', [
        'destination' => $destination,
        'location' => null,
        'action' => route('admin.destinations.locations.store', $destination),
        'method' => 'POST',
    ])
@endsection
