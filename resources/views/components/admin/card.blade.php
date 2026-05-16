@props(['title' => null])

<section class="admin-card">
    @if ($title || isset($headerActions))
        <header class="admin-card__header">
            @if ($title)
                <h2 class="admin-card__title">{{ $title }}</h2>
            @endif
            @isset($headerActions)
                <div>{{ $headerActions }}</div>
            @endisset
        </header>
    @endif

    {{ $slot }}
</section>
