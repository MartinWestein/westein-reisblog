@extends('layouts.admin')

@section('title', __('Nieuw familielid'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.family-members.index') }}">{{ __('Familieleden') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Nieuw') }}</span>
@endsection

@section('content')
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-header__title">{{ __('Nieuw familielid') }}</h1>
            <p class="admin-page-header__subtitle">
                {{ __('Voeg een profiel toe voor op de Over ons-pagina.') }}
            </p>
        </div>
    </div>

    @include('admin.family-members._form', ['member' => $member])
@endsection
