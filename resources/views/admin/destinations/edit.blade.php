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

    {{-- Gallery-beheer verschijnt hier in de volgende stap (na de gallery-component) --}}
@endsection
