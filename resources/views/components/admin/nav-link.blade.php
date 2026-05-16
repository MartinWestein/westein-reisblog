@props(['route', 'icon', 'label'])

@php
    $exists = \Illuminate\Support\Facades\Route::has($route);
    $href = $exists ? route($route) : '#';
    $active = $exists && (request()->routeIs($route) || request()->routeIs(\Illuminate\Support\Str::beforeLast($route, '.') . '.*'));
    $classes = 'admin-sidebar__link' . ($active ? ' is-active' : '');
@endphp

<a href="{{ $href }}" class="{{ $classes }}" title="{{ $label }}" @if (! $exists) aria-disabled="true" @endif>
    <i class="bi {{ $icon }}"></i>
    <span class="admin-sidebar__link-label">{{ $label }}</span>
</a>
