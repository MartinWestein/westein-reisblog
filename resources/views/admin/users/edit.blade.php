@extends('layouts.admin')

@section('title', __('Gebruiker bewerken'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $user->name }}</h1>
            <p class="text-muted mb-0">
                {{ $user->email }}
            </p>
        </div>
    </div>

    <div class="alert alert-info">
        {{ __('Deze pagina wordt geïmplementeerd in blok 4.13.d (edit + rollen + guards).') }}
    </div>
@endsection
