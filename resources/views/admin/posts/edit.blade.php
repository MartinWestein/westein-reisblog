@extends('layouts.admin')

@section('title', __('Post bewerken'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.posts.index') }}">Posts</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ $post->title }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="$post->title"
        :subtitle="__('Post bewerken')"
    />

    @include('admin.posts._form', [
        'post' => $post,
        'action' => route('admin.posts.update', $post),
        'method' => 'PUT',
    ])
@endsection
