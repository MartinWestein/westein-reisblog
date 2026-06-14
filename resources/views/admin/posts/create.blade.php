@extends('layouts.admin')

@section('title', __('Nieuwe post'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.posts.index') }}">Posts</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Nieuwe post') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="__('Nieuwe post')"
        :subtitle="__('Schrijf een nieuw reisverslag of reistip.')"
    />

    @php $post = null; @endphp
    @include('admin.posts._form', [
        'post' => $post,
        'action' => route('admin.posts.store'),
        'method' => 'POST',
    ])
@endsection
