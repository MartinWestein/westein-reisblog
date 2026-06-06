@extends('layouts.admin')

@section('title', __('Nieuwe bestemming'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.destinations.index') }}">{{ __('Bestemmingen') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Nieuw') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="__('Nieuwe bestemming')"
        :subtitle="__('Voeg een land of regio toe.')"
    />

    @include('admin.destinations._form', [
        'destination' => null,
        'action' => route('admin.destinations.store'),
        'method' => 'POST',
    ])
@endsection
