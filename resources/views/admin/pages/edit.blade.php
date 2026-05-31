@extends('layouts.admin')

@section('title', __('Pagina bewerken'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.pages.index') }}">Pagina's</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ $page->title }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="$page->title"
        :subtitle="__('Pagina bewerken')"
    />

    @include('admin.pages._form', [
        'page' => $page,
        'action' => route('admin.pages.update', $page),
        'method' => 'PUT',
    ])
@endsection
