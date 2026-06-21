@extends('layouts.admin')

@section('title', __('Nieuwe abonnee'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.subscribers.index') }}">{{ __('Abonnees') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ __('Nieuw') }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="__('Nieuwe abonnee')"
        :subtitle="__('Voeg een e-mailadres handmatig toe. Een bevestigingsmail wordt automatisch verstuurd.')" />

    <x-admin.form-layout :action="route('admin.subscribers.store')" method="POST">
        <x-slot:main>
            @include('admin.subscribers._form')
        </x-slot:main>

        <x-slot:actions>
            <a href="{{ route('admin.subscribers.index') }}" class="btn btn-link">
                {{ __('Annuleren') }}
            </a>
            <button type="submit" class="btn btn-primary">
                {{ __('Toevoegen en bevestigingsmail versturen') }}
            </button>
        </x-slot:actions>
    </x-admin.form-layout>

    <hr id="import" class="my-5">

    <x-admin.card :title="__('Of: importeer meerdere abonnees uit een CSV-bestand')">
        <p class="text-muted small mb-3">
            {{ __('Het bestand moet twee kolommen hebben: ') }}
            <code>email</code> {{ __('en') }} <code>name</code>.
            {{ __('Geïmporteerde abonnees komen op status “wacht op bevestiging”. Eerder uitgeschreven adressen worden overgeslagen.') }}
            <a href="{{ route('admin.subscribers.import-template') }}" class="ms-2">
                <i class="bi bi-download me-1"></i>{{ __('Download voorbeeldbestand') }}
            </a>
        </p>

        <form method="POST" action="{{ route('admin.subscribers.import') }}"
              enctype="multipart/form-data" class="d-flex gap-2 align-items-start flex-wrap">
            @csrf
            <div class="flex-grow-1" style="min-width: 18rem;">
                <input type="file" name="file" accept=".csv,text/csv,text/plain"
                       class="form-control @error('file') is-invalid @enderror" required>
                @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-upload me-1"></i>{{ __('Importeer') }}
            </button>
        </form>
    </x-admin.card>
@endsection
