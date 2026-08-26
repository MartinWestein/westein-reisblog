@props(['items' => []])
@if (! empty($items))
    <nav class="public-breadcrumb" aria-label="Kruimelspoor">
        <div class="container">
            <ol class="public-breadcrumb__list">
                @foreach ($items as $item)
                    <li class="public-breadcrumb__item">
                        @if (isset($item['url']) && ! $loop->last)
                            <a href="{{ $item['url'] }}" class="public-breadcrumb__link">{{ $item['label'] }}</a>
                        @else
                            <span class="public-breadcrumb__current" aria-current="page">{{ $item['label'] }}</span>
                        @endif
                        @unless ($loop->last)
                            <span class="public-breadcrumb__separator" aria-hidden="true">/</span>
                        @endunless
                    </li>
                @endforeach
            </ol>
        </div>
    </nav>
    @php
        $__pos = 0;
        $__crumbElements = [];
        foreach ($items as $__crumb) {
            $__pos++;
            $__element = [
                '@type' => 'ListItem',
                'position' => $__pos,
                'name' => $__crumb['label'],
            ];
            if (! empty($__crumb['url'])) {
                $__element['item'] = $__crumb['url'];
            }
            $__crumbElements[] = $__element;
        }
        $__breadcrumbLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $__crumbElements,
        ];
    @endphp
    <x-public.json-ld :data="$__breadcrumbLd" />
@endif
{{-- EOF --}}