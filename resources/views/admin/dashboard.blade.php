@extends('layouts.admin')

@section('title', __('Dashboard'))

@section('content')
    <x-admin.page-header
        :title="__('Welkom terug, ') . auth()->user()->name"
        :subtitle="__('Overzicht van het Westein Reis Blog beheer.')"
    />

    {{-- KPI cards: 6 in twee rijen --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-4">
            <x-admin.stat-card
                :label="__('Gepubliceerde posts')"
                :value="$stats['posts_published']['value']"
                :delta="$stats['posts_published']['delta']"
                :route="$stats['posts_published']['route']"
                icon="bi-journal-check"
                tone="positive"
            />
        </div>
        <div class="col-md-6 col-lg-4">
            <x-admin.stat-card
                :label="__('Concepten')"
                :value="$stats['drafts']['value']"
                :delta="$stats['drafts']['delta']"
                :route="$stats['drafts']['route']"
                icon="bi-file-earmark-text"
            />
        </div>
        <div class="col-md-6 col-lg-4">
            <x-admin.stat-card
                :label="__('Reacties (totaal)')"
                :value="$stats['comments_total']['value']"
                :delta="$stats['comments_total']['delta']"
                :route="$stats['comments_total']['route']"
                icon="bi-chat-left-dots"
            />
        </div>

        <div class="col-md-6 col-lg-4">
            <x-admin.stat-card
                :label="__('Te modereren')"
                :value="$stats['comments_pending']['value']"
                :delta="$stats['comments_pending']['delta']"
                :route="$stats['comments_pending']['route']"
                icon="bi-shield-exclamation"
                :tone="$stats['comments_pending']['value'] > 0 ? 'warning' : 'neutral'"
            />
        </div>
        <div class="col-md-6 col-lg-4">
            <x-admin.stat-card
                :label="__('Abonnees')"
                :value="$stats['subscribers']['value']"
                :delta="$stats['subscribers']['delta']"
                :route="$stats['subscribers']['route']"
                icon="bi-envelope-at"
            />
        </div>
        <div class="col-md-6 col-lg-4">
            <x-admin.stat-card
                :label="__('Geplande brieven')"
                :value="$stats['newsletters_scheduled']['value']"
                :route="$stats['newsletters_scheduled']['route']"
                icon="bi-calendar-event"
                :delta-label="__('staan ingepland')"
            />
        </div>
    </div>

    {{-- Activity feed --}}
    <x-admin.card :title="__('Recente activiteit')">
        @if ($activities->isEmpty())
            <div class="admin-activity__empty">
                <i class="bi bi-clock-history" style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;"></i>
                {{ __('Nog geen activiteit om te tonen.') }}
            </div>
        @else
            <div class="admin-activity">
                @foreach ($activities as $activity)
                    <div class="admin-activity__item">
                        <div class="admin-activity__icon">
                            <i class="bi {{ $activity['icon'] }}"></i>
                        </div>
                        <div class="admin-activity__body">
                            <p class="admin-activity__text">
                                <strong>{{ $activity['actor'] }}</strong>
                                {{ $activity['verb'] }}
                                @if ($activity['subject'])
                                    <span class="admin-activity__subject">{{ $activity['subject'] }}</span>
                                @endif
                            </p>
                            <div class="admin-activity__meta">
                                <span>{{ $activity['at']?->diffForHumans() }}</span>
                                @if (! empty($activity['badge']))
                                    <span class="admin-activity__badge">{{ $activity['badge'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-admin.card>
@endsection
