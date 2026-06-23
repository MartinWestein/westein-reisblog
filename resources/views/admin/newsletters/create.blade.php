@extends('layouts.admin')

@section('title', __('Nieuwe nieuwsbrief'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.newsletters.index') }}">{{ __('Nieuwsbrieven') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Nieuw') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="__('Nieuwe nieuwsbrief')"
        :subtitle="__('Stel een concept samen. Verzenden kan na opslaan.')"
    />

    @php $newsletter = null; @endphp
    @include('admin.newsletters._form', [
        'newsletter' => $newsletter,
        'action' => route('admin.newsletters.store'),
        'method' => 'POST',
    ])
@endsection
