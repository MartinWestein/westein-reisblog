@props(['user'])

@if ($user->deactivated_at)
    <span class="badge bg-secondary" title="{{ __('Gedeactiveerd op :date', ['date' => $user->deactivated_at->isoFormat('D MMM YYYY')]) }}">
        <i class="bi bi-person-slash me-1"></i>{{ __('Gedeactiveerd') }}
    </span>
@else
    <span class="badge bg-success-subtle text-success">
        <i class="bi bi-check-circle me-1"></i>{{ __('Actief') }}
    </span>
@endif
