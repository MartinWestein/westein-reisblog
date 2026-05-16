@extends('layouts.admin')

@section('title', __('Nieuwe categorie'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.categories.index') }}">{{ __('Categorieën') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Nieuw') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="__('Nieuwe categorie')"
        :subtitle="__('Voeg een nieuwe indeling toe voor blogposts.')"
    />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.categories.store') }}" novalidate>
            @csrf
            @include('admin.categories._form')

            <x-admin.submit :label="__('Aanmaken')" :cancel-route="route('admin.categories.index')" />
        </form>
    </x-admin.card>
@endsection
