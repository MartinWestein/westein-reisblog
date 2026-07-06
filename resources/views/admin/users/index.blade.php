@extends('layouts.admin')

@section('title', __('Gebruikers'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('Gebruikers') }}</h1>
            <p class="text-muted mb-0">
                {{ __('Beheer accounts, rollen en toegang.') }}
            </p>
        </div>
    </div>

    <div class="alert alert-info">
        {{ __('Deze pagina wordt geïmplementeerd in blok 4.13.b (index + filters + tabel).') }}
    </div>
@endsection
