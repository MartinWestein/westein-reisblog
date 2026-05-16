@props(['sort', 'currentSort', 'currentDirection'])

@php
    $isActive = $sort === $currentSort;
    $nextDirection = ($isActive && $currentDirection === 'asc') ? 'desc' : 'asc';
    $params = array_merge(request()->query(), ['sort' => $sort, 'direction' => $nextDirection]);
    $href = url()->current().'?'.http_build_query($params);
@endphp

<a href="{{ $href }}" class="text-decoration-none {{ $isActive ? 'fw-bold text-body' : 'text-muted' }}">
    {{ $slot }}
    @if ($isActive)
        <i class="bi bi-arrow-{{ $currentDirection === 'asc' ? 'up' : 'down' }}-short"></i>
    @endif
</a>
