@extends('layouts.admin')

@section('title', __('Nieuwsbrief').' â€¢ '.$newsletter->subject)

@section('breadcrumbs')
    <a href="{{ route('admin.home') }}">{{ __('Beheer') }}</a>
    <span class="admin-breadcrumbs__separator">/</span>
    <a href="{{ route('admin.newsletters.index') }}">{{ __('Nieuwsbrieven') }}</a>
    <span class="admin-breadcrumbs__separator">/</span>
    <span class="admin-breadcrumbs__current">{{ $newsletter->subject }}</span>
@endsection

@section('content')
    @php
        $statusBadgeClass = match ($newsletter->status) {
            'draft' => 'bg-secondary',
            'sending' => 'bg-info text-dark',
            'sent' => 'bg-success',
            default => 'bg-light text-dark',
        };
        $statusBadgeLabel = match ($newsletter->status) {
            'draft' => __('Concept'),
            'sending' => __('Wordt verzonden'),
            'sent' => __('Verzonden'),
            default => $newsletter->status,
        };
    @endphp

    {{-- Header met meta + status --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 mb-2">{{ $newsletter->subject }}</h1>
            <div class="d-flex flex-wrap gap-3 small text-muted">
                <span><i class="bi bi-person"></i> {{ $newsletter->author?->name ?? 'â€”' }}</span>
                <span><i class="bi bi-calendar"></i> {{ $newsletter->created_at->isoFormat('D MMM YYYY HH:mm') }}</span>
                @if ($newsletter->sent_at)
                    <span><i class="bi bi-send-check"></i> {{ __('Verzonden') }}: {{ $newsletter->sent_at->isoFormat('D MMM YYYY HH:mm') }}</span>
                @endif
                <span class="badge {{ $statusBadgeClass }}">{{ $statusBadgeLabel }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if ($newsletter->isEditable())
                <a href="{{ route('admin.newsletters.edit', $newsletter) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-pencil"></i> {{ __('Bewerken') }}
                </a>
            @endif
            <a href="{{ route('admin.newsletters.index') }}" class="btn btn-link btn-sm text-muted">
                <i class="bi bi-arrow-left"></i> {{ __('Terug') }}
            </a>
        </div>
    </div>

    {{-- KPI-blok --}}
    @if ($newsletter->isDraft())
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle"></i>
            {{ __('Deze nieuwsbrief is nog niet verzonden. Audit-gegevens verschijnen hier zodra verzending start.') }}
        </div>
    @else
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase mb-1">{{ __('Totaal') }}</div>
                        <div class="h3 mb-0">{{ $stats->total }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-success">
                    <div class="card-body">
                        <div class="text-success small text-uppercase mb-1"><i class="bi bi-check-circle"></i> {{ __('Bezorgd') }}</div>
                        <div class="h3 mb-0 text-success">{{ $stats->delivered }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-danger">
                    <div class="card-body">
                        <div class="text-danger small text-uppercase mb-1"><i class="bi bi-x-circle"></i> {{ __('Mislukt') }}</div>
                        <div class="h3 mb-0 text-danger">{{ $stats->failed }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-warning">
                    <div class="card-body">
                        <div class="text-warning small text-uppercase mb-1"><i class="bi bi-hourglass-split"></i> {{ __('In wachtrij') }}</div>
                        <div class="h3 mb-0 text-warning">{{ $stats->pending }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Filter + tabel --}}
    @unless ($newsletter->isDraft())
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="h6 mb-0">{{ __('Verzendingen') }}</h2>
                <form method="GET" action="{{ route('admin.newsletters.show', $newsletter) }}" class="d-flex gap-2">
                    <select name="status" class="form-select form-select-sm" style="min-width: 12rem;"
                            onchange="this.form.submit()">
                        <option value="all" @selected($statusFilter === 'all')>
                            {{ __('Alle statussen') }} ({{ $stats->total }})
                        </option>
                        <option value="delivered" @selected($statusFilter === 'delivered')>
                            {{ __('Bezorgd') }} ({{ $stats->delivered }})
                        </option>
                        <option value="failed" @selected($statusFilter === 'failed')>
                            {{ __('Mislukt') }} ({{ $stats->failed }})
                        </option>
                        <option value="pending" @selected($statusFilter === 'pending')>
                            {{ __('In wachtrij') }} ({{ $stats->pending }})
                        </option>
                    </select>
                </form>
            </div>

            @if ($sends->isEmpty())
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                    <p class="mb-0 mt-2">{{ __('Geen verzendingen in deze categorie.') }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Abonnee') }}</th>
                                <th>{{ __('E-mailadres') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>
                                    <x-admin.sort-link sort="sent_at" :current-sort="$sort" :current-direction="$direction">
                                        {{ __('Bezorgd op') }}
                                    </x-admin.sort-link>
                                </th>
                                <th>
                                    <x-admin.sort-link sort="failed_at" :current-sort="$sort" :current-direction="$direction">
                                        {{ __('Mislukt op') }}
                                    </x-admin.sort-link>
                                </th>
                                <th>{{ __('Fout') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sends as $send)
                                <tr>
                                    <td>{{ $send->subscriber?->name ?? 'â€”' }}</td>
                                    <td><code class="small">{{ $send->subscriber?->email ?? 'â€”' }}</code></td>
                                    <td>
                                        @if ($send->sent_at)
                                            <span class="badge bg-success">{{ __('Bezorgd') }}</span>
                                        @elseif ($send->failed_at)
                                            <span class="badge bg-danger">{{ __('Mislukt') }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ __('In wachtrij') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $send->sent_at?->isoFormat('D MMM HH:mm') ?? 'â€”' }}</td>
                                    <td>{{ $send->failed_at?->isoFormat('D MMM HH:mm') ?? 'â€”' }}</td>
                                    <td class="small text-muted" style="max-width: 24rem;">
                                        @if ($send->error)
                                            <span title="{{ $send->error }}">{{ \Illuminate\Support\Str::limit($send->error, 60) }}</span>
                                        @else
                                            â€”
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($sends->hasPages())
                    <div class="card-footer">
                        {{ $sends->links() }}
                    </div>
                @endif
            @endif
        </div>
    @endunless
@endsection
