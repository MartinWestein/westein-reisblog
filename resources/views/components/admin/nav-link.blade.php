@props(['route', 'icon', 'label', 'can' => null])
@php
    // Permission-check: als $can gezet is en user heeft het niet, render niets.
    // Guests (auth()->user() == null) zien alleen items zonder $can-eis.
    if ($can !== null && ! (auth()->user()?->can($can) ?? false)) {
        return;
    }

    $exists = \Illuminate\Support\Facades\Route::has($route);
    $href = $exists ? route($route) : '#';
    $active = $exists && (request()->routeIs($route) || request()->routeIs(\Illuminate\Support\Str::beforeLast($route, '.') . '.*'));
    $classes = 'admin-sidebar__link' . ($active ? ' is-active' : '');
@endphp
<a href="{{ $href }}" class="{{ $classes }}" title="{{ $label }}" @if (! $exists) aria-disabled="true" @endif>
    <i class="bi {{ $icon }}"></i>
    <span class="admin-sidebar__link-label">{{ $label }}</span>
</a>
