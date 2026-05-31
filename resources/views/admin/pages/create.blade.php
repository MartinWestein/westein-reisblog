@extends('layouts.admin')

@section('title', __('Nieuwe pagina'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.pages.index') }}">Pagina's</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Nieuwe pagina') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="__('Nieuwe pagina')"
        :subtitle="__('Maak een statische pagina aan voor het publieke deel van de site.')"
    />

    @php $page = null; @endphp
    @include('admin.pages._form', [
        'page' => $page,
        'action' => route('admin.pages.store'),
        'method' => 'POST',
    ])
@endsection
