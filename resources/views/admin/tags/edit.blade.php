@extends('layouts.admin')

@section('title', __('Tag bewerken'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.tags.index') }}">{{ __('Tags') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ $tag->name }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="$tag->name"
        :subtitle="__('Bewerk de naam van deze tag. De slug blijft ongewijzigd.')"
    />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.tags.update', $tag) }}" novalidate>
            @csrf
            @method('PUT')

            @include('admin.tags._form', ['tag' => $tag])

            <x-admin.submit :label="__('Wijzigingen opslaan')" :cancel-route="route('admin.tags.index')" />
        </form>
    </x-admin.card>
@endsection
