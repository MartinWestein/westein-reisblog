@extends('layouts.admin')

@section('title', __('Categorie bewerken'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.categories.index') }}">{{ __('Categorieën') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ $category->name }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="$category->name"
        :subtitle="__('Bewerk de details van deze categorie.')"
    />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" novalidate>
            @csrf
            @method('PUT')

            @include('admin.categories._form', ['category' => $category])

            <x-admin.submit :label="__('Wijzigingen opslaan')" :cancel-route="route('admin.categories.index')" />
        </form>
    </x-admin.card>
@endsection
