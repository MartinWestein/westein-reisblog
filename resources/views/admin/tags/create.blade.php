@extends('layouts.admin')

@section('title', __('Nieuwe tag'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.tags.index') }}">{{ __('Tags') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Nieuw') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="__('Nieuwe tag')"
        :subtitle="__('Voeg een vrij label toe voor blogposts.')"
    />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.tags.store') }}" novalidate>
            @csrf
            @include('admin.tags._form')

            <x-admin.submit :label="__('Aanmaken')" :cancel-route="route('admin.tags.index')" />
        </form>
    </x-admin.card>
@endsection
