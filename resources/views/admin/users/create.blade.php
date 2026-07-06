@extends('layouts.admin')

@section('title', __('Nieuwe gebruiker'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('Nieuwe gebruiker') }}</h1>
            <p class="text-muted mb-0">
                {{ __('Verstuur een uitnodiging per e-mail.') }}
            </p>
        </div>
    </div>

    <div class="alert alert-info">
        {{ __('Deze pagina wordt geïmplementeerd in blok 4.13.c (create + invite-flow).') }}
    </div>
@endsection
