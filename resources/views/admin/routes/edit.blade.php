@extends('layouts.admin')

@section('title', __('Reisroute bewerken'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.reisroutes.index') }}">{{ __('Reisroutes') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ $route->name }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="$route->name"
        :subtitle="__('Reisroute bewerken')"
    />

    @include('admin.routes._form', [
        'route' => $route,
        'action' => route('admin.reisroutes.update', $route),
        'method' => 'PUT',
    ])
@endsection
