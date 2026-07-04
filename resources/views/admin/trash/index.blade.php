@extends('layouts.admin')

@section('title', __('Prullenbak'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('Prullenbak') }}</h1>
            <p class="text-muted mb-0">
                {{ __('Verwijderde items — herstel of definitief wissen.') }}
            </p>
        </div>
    </div>

    <div class="text-center py-5">
        <i class="bi bi-trash text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-3 mb-0">
            {{ __('Nog geen items in de prullenbak.') }}
        </p>
    </div>
@endsection
