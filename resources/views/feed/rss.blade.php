{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ $feedTitle }}</title>
        <link>{{ $feedLink }}</link>
        <description>{{ $feedDescription }}</description>
        <language>nl-NL</language>
        <atom:link href="{{ $feedUrl }}" rel="self" type="application/rss+xml" />
        @foreach ($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ $post->url() }}</link>
            <guid isPermaLink="true">{{ $post->url() }}</guid>
            <pubDate>{{ optional($post->published_at)->toRssString() }}</pubDate>
            <description>{{ Str::limit(strip_tags($post->excerpt ?? ''), 300) }}</description>
        </item>
        @endforeach
    </channel>
</rss>