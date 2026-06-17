@extends('layouts.admin')

@section('title', __('Nieuwe reisroute'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.reisroutes.index') }}">{{ __('Reisroutes') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Nieuwe reisroute') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        title="{{ __('Nieuwe reisroute') }}"
        subtitle="{{ __('Maak een nieuwe reisroute aan en voeg waypoints toe.') }}"
    />

    @include('admin.routes._form', [
        'route' => null,
        'action' => route('admin.reisroutes.store'),
        'method' => 'POST',
    ])
@endsection
