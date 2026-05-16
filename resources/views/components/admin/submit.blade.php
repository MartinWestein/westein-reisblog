@props(['label' => 'Opslaan', 'cancelRoute' => null])

<div class="admin-form-actions">
    @if ($cancelRoute)
        <a href="{{ $cancelRoute }}" class="btn btn-outline-secondary">{{ __('Annuleren') }}</a>
    @endif
    <button type="submit" class="btn btn-primary">{{ $label }}</button>
</div>
