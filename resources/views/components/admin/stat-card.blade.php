@props([
    'label',
    'value',
    'delta' => null,
    'deltaLabel' => 'deze week',
    'route' => null,
    'icon' => null,
    'tone' => 'neutral', // neutral | warning | positive
])

@php
    $href = null;
    if ($route) {
        try {
            $href = route($route);
        } catch (\Exception $e) {
            $href = null;
        }
    }

    $toneClass = match ($tone) {
        'warning' => 'admin-stat--warning',
        'positive' => 'admin-stat--positive',
        default => '',
    };
@endphp

<div class="admin-stat {{ $toneClass }}">
    <div class="admin-stat__label">
        @if ($icon)<i class="bi {{ $icon }}"></i>@endif
        {{ $label }}
    </div>

    <div class="admin-stat__value">{{ number_format($value, 0, ',', '.') }}</div>

    @if (! is_null($delta))
        <div class="admin-stat__delta">
            @if ($delta > 0)
                <span class="admin-stat__delta-positive">
                    <i class="bi bi-arrow-up-short"></i>{{ $delta }}
                </span>
            @elseif ($delta === 0)
                <span class="admin-stat__delta-neutral">±0</span>
            @else
                <span class="admin-stat__delta-negative">
                    <i class="bi bi-arrow-down-short"></i>{{ abs($delta) }}
                </span>
            @endif
            {{ $deltaLabel }}
        </div>
    @endif

    @if ($href)
        <a href="{{ $href }}" class="admin-stat__link">
            {{ __('Bekijken') }} <i class="bi bi-arrow-right-short"></i>
        </a>
    @endif
</div>
