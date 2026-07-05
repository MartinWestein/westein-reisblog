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
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Titel') }}</th>
                            <th style="width: 130px;">{{ __('Type') }}</th>
                            <th style="width: 200px;">{{ __('Verwijderd') }}</th>
                            <th class="text-end" style="width: 100px;">{{ __('Acties') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
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
    @endif
@endsection
