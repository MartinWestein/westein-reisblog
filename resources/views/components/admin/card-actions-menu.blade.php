@props([
    'editUrl',
    'deleteUrl',
    'deleteConfirm' => 'Weet je zeker dat je dit item wilt verwijderen?',
])

<div
    class="card-actions-menu"
    x-data="{ open: false, confirming: false }"
    @click.outside="open = false; confirming = false"
    @keydown.escape.window="open = false; confirming = false"
>
    <button
        type="button"
        class="card-actions-menu__trigger"
        @click="open = !open; confirming = false"
        :aria-expanded="open.toString()"
        aria-haspopup="menu"
        aria-label="{{ __('Acties') }}"
    >
        <i class="bi bi-three-dots-vertical" aria-hidden="true"></i>
    </button>

    <div
        class="card-actions-menu__dropdown"
        x-show="open"
        x-transition.opacity.duration.150ms
        x-cloak
        role="menu"
    >
        <a href="{{ $editUrl }}" class="card-actions-menu__item" role="menuitem">
            <i class="bi bi-pencil" aria-hidden="true"></i>
            <span>{{ __('Bewerken') }}</span>
        </a>

        <button
            type="button"
            class="card-actions-menu__item card-actions-menu__item--danger"
            role="menuitem"
            x-show="!confirming"
            @click="confirming = true"
        >
            <i class="bi bi-trash" aria-hidden="true"></i>
            <span>{{ __('Verwijderen') }}</span>
        </button>

        <form
            method="POST"
            action="{{ $deleteUrl }}"
            x-show="confirming"
            x-cloak
            class="card-actions-menu__confirm"
        >
            @csrf
            @method('DELETE')
            <p class="card-actions-menu__confirm-text">{{ $deleteConfirm }}</p>
            <div class="card-actions-menu__confirm-actions">
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="confirming = false; open = false">
                    {{ __('Annuleren') }}
                </button>
                <button type="submit" class="btn btn-sm btn-danger">
                    {{ __('Verwijderen') }}
                </button>
            </div>
        </form>
    </div>
</div>
