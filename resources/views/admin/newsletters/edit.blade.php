@extends('layouts.admin')

@section('title', __('Nieuwsbrief bewerken'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.newsletters.index') }}">{{ __('Nieuwsbrieven') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ $newsletter->subject }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="$newsletter->subject"
        :subtitle="__('Nieuwsbrief bewerken')"
    />

    @include('admin.newsletters._form', [
        'newsletter' => $newsletter,
        'action' => route('admin.newsletters.update', $newsletter),
        'method' => 'PUT',
    ])
@endsection
