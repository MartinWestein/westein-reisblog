@extends('layouts.admin')

@section('title', 'Reacties')

@section('content')
    <x-admin.page-header
        title="Reacties"
        subtitle="Modereer reacties die lezers achterlaten op posts."
    />

    <x-admin.card>
        <form method="GET" action="{{ route('admin.comments.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
            <input
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="{{ __('Zoek in auteur of tekst…') }}"
                class="form-control"
                style="max-width: 280px;"
            >

            <select name="status" class="form-select" style="max-width: 220px;" onchange="this.form.submit()">
                @php
                    $statusOptions = [
                        'pending'  => __('Te modereren'),
                        'approved' => __('Goedgekeurd'),
                        'rejected' => __('Afgekeurd'),
                        'spam'     => __('Spam'),
                        'all'      => __('Alle reacties'),
                    ];
                @endphp
                @foreach ($statusOptions as $key => $label)
                    @php
                        $count = $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0);
                    @endphp
                    <option value="{{ $key }}" @selected($status === $key)>
                        {{ $label }} ({{ $count }})
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary">{{ __('Filteren') }}</button>

            @if (request('q') || $status !== 'pending')
                <a href="{{ route('admin.comments.index') }}" class="btn btn-link text-muted">
                    {{ __('Wissen') }}
                </a>
            @endif
        </form>
    </x-admin.card>

    @if ($comments->isEmpty())
        <x-admin.card class="mt-3">
            <p class="text-muted m-0">
                @if (request('q') || $status !== 'pending')
                    {{ __('Geen reacties gevonden voor deze filters.') }}
                @else
                    {{ __('Er zijn op dit moment geen reacties om te modereren.') }}
                @endif
            </p>
        </x-admin.card>
    @else
        <x-admin.card class="mt-3 p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead>
                        <tr>
                            <th>{{ __('Auteur') }}</th>
                            <th>{{ __('Post') }}</th>
                            <th>{{ __('Reactie') }}</th>
                            <th class="text-nowrap">
                                <x-admin.sort-link sort="created_at" :current-sort="$sort" :current-direction="$direction">
                                    {{ __('Datum') }}
                                </x-admin.sort-link>
                            </th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Acties') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($comments as $comment)
                            <tr x-data="{ open: false }">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <x-admin.avatar-initials :subject="$comment->author" :size="32" />
                                        <span>{{ $comment->author->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.posts.edit', $comment->post) }}" class="text-decoration-none">
                                        {{ \Illuminate\Support\Str::limit($comment->post->title, 40) }}
                                    </a>
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-link p-0 text-start text-decoration-none text-body"
                                        @click="open = !open"
                                        :aria-expanded="open.toString()"
                                    >
                                        <span x-show="!open">{{ \Illuminate\Support\Str::limit($comment->body, 80) }}</span>
                                        <span x-show="open" x-cloak style="white-space: pre-wrap;">{{ $comment->body }}</span>
                                    </button>
                                </td>
                                <td class="text-nowrap text-muted small">
                                    {{ $comment->created_at->isoFormat('D MMM YYYY HH:mm') }}
                                </td>
                                <td>
                                    <x-admin.comment-status-badge :status="$comment->status" />
                                </td>
                                <td class="text-end">
                                    <x-admin.comment-actions :comment="$comment" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>

        <div class="mt-3">
            {{ $comments->withQueryString()->links() }}
        </div>
    @endif
@endsection
