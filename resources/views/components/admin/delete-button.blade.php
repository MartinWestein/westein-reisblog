@props([
    'action',
    'label' => 'Verwijderen',
    'disabled' => false,
    'disabledReason' => null,
])

@if ($disabled)
    <span
        class="d-inline-block"
        tabindex="0"
        data-bs-toggle="tooltip"
        title="{{ $disabledReason ?: $label }}"
    >
        <button
            type="button"
            class="btn btn-sm btn-outline-danger"
            disabled
            style="pointer-events: none;"
        >
            <i class="bi bi-trash"></i>
        </button>
    </span>
@else
    <form
        method="POST"
        action="{{ $action }}"
        x-data="{ confirming: false }"
        class="d-inline"
        @click.outside="confirming = false"
    >
        @csrf
        @method('DELETE')
        <button
            type="button"
            class="btn btn-sm btn-outline-danger"
            x-show="!confirming"
            @click="confirming = true"
            title="{{ $label }}"
        >
            <i class="bi bi-trash"></i>
        </button>
        <button
            type="submit"
            class="btn btn-sm btn-danger"
            x-show="confirming"
            x-cloak
            x-transition.opacity
        >
            <i class="bi bi-exclamation-triangle"></i> {{ __('Zeker?') }}
        </button>
    </form>
@endif
