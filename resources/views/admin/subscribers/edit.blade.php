@extends('layouts.admin')

@section('title', __('Abonnee bewerken'))

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <i class="bi bi-chevron-right"></i>
    <a href="{{ route('admin.subscribers.index') }}">{{ __('Abonnees') }}</a>
    <i class="bi bi-chevron-right"></i>
    <span class="admin-breadcrumbs__current">{{ $subscriber->email }}</span>
@endsection

@section('content')
    <x-admin.page-header
        :title="$subscriber->email"
        :subtitle="__('Bewerk de gegevens van deze abonnee. Status veranderen kan via de bulk-acties op de overzichtspagina.')" />

    <x-admin.form-layout :action="route('admin.subscribers.update', $subscriber)" method="PUT">
        <x-slot:main>
            @include('admin.subscribers._form', ['subscriber' => $subscriber])
        </x-slot:main>

        <x-slot:side>
            <x-admin.form-section :title="__('Status')">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <x-admin.subscriber-status-badge :status="$subscriber->status()" />
                </div>
                <dl class="row mb-0 small">
                    <dt class="col-sm-5">{{ __('Aangemeld op') }}</dt>
                    <dd class="col-sm-7">{{ $subscriber->created_at->isoFormat('D MMM YYYY') }}</dd>

                    <dt class="col-sm-5">{{ __('Bevestigd op') }}</dt>
                    <dd class="col-sm-7">
                        {{ $subscriber->confirmed_at?->isoFormat('D MMM YYYY') ?? '—' }}
                    </dd>

                    <dt class="col-sm-5">{{ __('Uitgeschreven op') }}</dt>
                    <dd class="col-sm-7">
                        {{ $subscriber->unsubscribed_at?->isoFormat('D MMM YYYY') ?? '—' }}
                    </dd>
                </dl>
            </x-admin.form-section>
        </x-slot:side>

        <x-slot:actions>
            <a href="{{ route('admin.subscribers.index') }}" class="btn btn-link">
                {{ __('Annuleren') }}
            </a>
            <button type="submit" class="btn btn-primary">
                {{ __('Opslaan') }}
            </button>
        </x-slot:actions>
    </x-admin.form-layout>
@endsection
