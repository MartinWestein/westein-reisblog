@props([
    'action',
    'method' => 'POST',
    'enctype' => null,
])

<form
    method="POST"
    action="{{ $action }}"
    @if ($enctype) enctype="{{ $enctype }}" @endif
    class="admin-form-layout"
    novalidate
>
    @csrf

    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="admin-form-layout__grid">
        <div class="admin-form-layout__main">
            {{ $main }}
        </div>

        @isset($side)
            <aside class="admin-form-layout__side">
                {{ $side }}
            </aside>
        @endisset
    </div>

    @isset($actions)
        <div class="admin-form-layout__actions">
            {{ $actions }}
        </div>
    @else
        <div class="admin-form-layout__actions">
            <a href="{{ url()->previous() }}" class="btn btn-link">{{ __('Annuleren') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Opslaan') }}</button>
        </div>
    @endisset
</form>
