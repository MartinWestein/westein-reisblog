@extends('emails.newsletter._layout')

@if ($newsletter->getFirstMediaUrl('header', 'medium'))
    @section('header')
        <img src="{{ $newsletter->getFirstMediaUrl('header', 'medium') }}" alt="" class="email-header-image" width="536">
    @endsection
@endif

@section('body')
    <h1>{{ $newsletter->subject }}</h1>

    {!! $newsletter->body !!}

    @if ($posts->isNotEmpty())
        <hr class="email-divider">

        <h2>{{ __('Recente verhalen') }}</h2>

        @foreach ($posts as $post)
            <div class="post-item">
                <h3 class="post-item-title">
                    <a href="{{ url('/'.$post->slug) }}">{{ $post->title }}</a>
                </h3>
                @if ($post->published_at)
                    <p class="post-item-meta">{{ $post->published_at->isoFormat('D MMMM YYYY') }}</p>
                @endif
                @if ($post->excerpt)
                    <p class="post-item-excerpt">{{ $post->excerpt }}</p>
                @endif
                <p>
                    <a href="{{ url('/'.$post->slug) }}" class="post-item-link">{{ __('Lees verder') }} &rarr;</a>
                </p>
            </div>
            @unless ($loop->last)
                <hr class="email-divider">
            @endunless
        @endforeach
    @endif
@endsection
