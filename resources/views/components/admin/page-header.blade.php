@props(['title', 'subtitle' => null])

<header class="admin-page-header">
    <div>
        <h1 class="admin-page-header__title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="admin-page-header__subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="admin-page-header__actions">
            {{ $actions }}
        </div>
    @endisset
</header>
