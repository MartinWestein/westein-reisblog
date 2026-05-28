@extends('layouts.admin')

@section('title', __('Bewerk :name', ['name' => $member->name]))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.family-members.index') }}">{{ __('Familieleden') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ $member->name }}</span>
@endsection

@section('content')
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-header__title">{{ $member->name }}</h1>
            <p class="admin-page-header__subtitle">
                {{ __('Aangemaakt op :date', ['date' => $member->created_at->translatedFormat('j F Y')]) }}
            </p>
        </div>
    </div>

    @include('admin.family-members._form', ['member' => $member])
@endsection
