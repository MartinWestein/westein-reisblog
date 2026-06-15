@props(['comment'])

<div class="d-inline-flex gap-1 align-items-center">
    @if ($comment->status !== 'approved')
        <form method="POST" action="{{ route('admin.comments.approve', $comment) }}" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm btn-outline-success" title="Goedkeuren">
                <i class="bi bi-check-lg" aria-hidden="true"></i>
                <span class="visually-hidden">Goedkeuren</span>
            </button>
        </form>
    @endif

    @if ($comment->status === 'pending')
        <form method="POST" action="{{ route('admin.comments.reject', $comment) }}" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Afkeuren">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
                <span class="visually-hidden">Afkeuren</span>
            </button>
        </form>
    @endif

    @if (in_array($comment->status, ['pending', 'rejected'], true))
        <form method="POST" action="{{ route('admin.comments.spam', $comment) }}" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm btn-outline-warning" title="Markeer als spam">
                <i class="bi bi-shield-exclamation" aria-hidden="true"></i>
                <span class="visually-hidden">Spam</span>
            </button>
        </form>
    @endif

    @can('delete', $comment)
        <x-admin.delete-button :action="route('admin.comments.destroy', $comment)" />
    @endcan
</div>
