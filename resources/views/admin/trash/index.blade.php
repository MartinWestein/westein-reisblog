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
        <div class="text-muted small">
            {{ $items->total() }} {{ Str::plural('item', $items->total()) }}
        </div>
    </div>

    <form method="GET" action="{{ route('admin.trash.index') }}" class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <label for="type" class="form-label small text-muted mb-1">{{ __('Type') }}</label>
                <select name="type" id="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('Alle types') }}</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if ($type)
                <div class="col-auto">
                    <a href="{{ route('admin.trash.index') }}" class="btn btn-sm btn-link">
                        {{ __('Filter wissen') }}
                    </a>
                </div>
            @endif
        </div>
    </form>

    @if ($items->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-trash text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-3 mb-0">
                @if ($type)
                    {{ __('Geen verwijderde items van dit type.') }}
                @else
                    {{ __('Nog geen items in de prullenbak.') }}
                @endif
            </p>
        </div>
    @else
        {{-- Reset de store bij elke page-load (analoog aan mediaSelection) --}}
        <div x-init="$store.trashSelection.reset()" class="d-none"></div>

        {{-- "Selecteer alle zichtbare"-control --}}
        <div class="mb-2">
            <label class="d-inline-flex align-items-center gap-2 small text-muted">
                <input
                    type="checkbox"
                    class="form-check-input m-0"
                    :checked="$store.trashSelection.allVisibleSelected()"
                    @change="$event.target.checked ? $store.trashSelection.selectAllVisible() : $store.trashSelection.clear()"
                >
                <span>{{ __('Selecteer alle zichtbare') }} ({{ $items->count() }})</span>
            </label>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>{{ __('Titel') }}</th>
                            <th style="width: 130px;">{{ __('Type') }}</th>
                            <th style="width: 200px;">{{ __('Verwijderd') }}</th>
                            <th class="text-end" style="width: 100px;">{{ __('Acties') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr data-trash-key="{{ $item->type }}:{{ $item->id }}">
                                <td>
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        :checked="$store.trashSelection.isSelected('{{ $item->type }}', {{ $item->id }})"
                                        @change="$store.trashSelection.toggle('{{ $item->type }}', {{ $item->id }})"
                                    >
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $item->title }}</div>
                                    @if ($item->context)
                                        <div class="small text-muted">{{ $item->context }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                        {{ $item->type_label }}
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ $item->deleted_at->diffForHumans() }}
                                </td>
                                <td class="text-end">
                                    <form
                                        method="POST"
                                        action="{{ route('admin.trash.restore', ['type' => $item->type, 'id' => $item->id]) }}"
                                        class="d-inline"
                                    >
                                        @csrf
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-success"
                                            title="{{ __('Herstellen') }}"
                                        >
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                    <x-admin.delete-button
                                        :action="route('admin.trash.force-delete', ['type' => $item->type, 'id' => $item->id])"
                                        label="Definitief verwijderen"
                                        :disabled="(bool) $item->blocked_reason"
                                        :disabled-reason="$item->blocked_reason"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $items->withQueryString()->links() }}
        </div>

        {{-- Sticky-bottom action bar --}}
        <div
            x-show="$store.trashSelection.hasSelection()"
            x-cloak
            x-transition.opacity
            class="media-action-bar"
        >
            <div class="media-action-bar__inner">
                <span class="text-muted">
                    <span x-text="$store.trashSelection.count()"></span>
                    <span x-text="$store.trashSelection.count() === 1 ? 'item' : 'items'"></span> {{ __('geselecteerd') }}
                </span>
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="$store.trashSelection.clear()">
                        {{ __('Selectie wissen') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#trashBulkRestoreModal">
                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Herstellen') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Hidden form voor bulk-restore submit --}}
        <form
            id="trash-bulk-restore-form"
            method="POST"
            action="{{ route('admin.trash.bulk-restore') }}"
            class="d-none"
        >
            @csrf
            <input type="hidden" name="items" value="">
        </form>

        {{-- Confirm-modal via @push('modals') (F4-N16) --}}
        @push('modals')
            <div
                class="modal fade"
                id="trashBulkRestoreModal"
                tabindex="-1"
                aria-labelledby="trashBulkRestoreModalLabel"
                aria-hidden="true"
                x-data
            >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title h5" id="trashBulkRestoreModalLabel">
                                {{ __('Items herstellen?') }}
                            </h2>
                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="{{ __('Sluiten') }}"
                            ></button>
                        </div>

                        <div class="modal-body">
                            <p class="mb-2">
                                <span x-text="$store.trashSelection.count()"></span>
                                {{ __('items terugzetten uit de prullenbak?') }}
                            </p>

                            <div class="alert alert-info small mb-0" role="alert">
                                {{ __('Als een geselecteerd item een verwijderde bestemming of locatie als ouder heeft, wordt die automatisch teruggezet.') }}
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                {{ __('Annuleren') }}
                            </button>
                            <button
                                type="button"
                                class="btn btn-success"
                                @click="$store.trashSelection.destroy()"
                            >
                                <i class="bi bi-arrow-counterclockwise"></i> {{ __('Herstellen') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endpush
    @endif
@endsection
