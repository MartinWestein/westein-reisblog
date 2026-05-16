@foreach (['success', 'error', 'info', 'warning'] as $type)
    @if (session($type))
        <div class="admin-flash admin-flash--{{ $type }}">
            <i class="bi bi-{{ ['success' => 'check-circle', 'error' => 'x-circle', 'info' => 'info-circle', 'warning' => 'exclamation-triangle'][$type] }}"></i>
            <div>{{ session($type) }}</div>
        </div>
    @endif
@endforeach

@if ($errors->any() && ! $errors->has('_form'))
    @php $hasFieldErrors = collect($errors->keys())->contains(fn($k) => $k !== '_form'); @endphp
    @if ($hasFieldErrors)
        <div class="admin-flash admin-flash--error">
            <i class="bi bi-x-circle"></i>
            <div>{{ __('Controleer de gemarkeerde velden hieronder.') }}</div>
        </div>
    @endif
@endif
