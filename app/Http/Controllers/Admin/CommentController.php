<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Comment::class);

        $status = $request->query('status', 'pending');
        if ($status !== 'all' && ! in_array($status, Comment::STATUSES, true)) {
            $status = 'pending';
        }

        $sort = in_array($request->query('sort'), ['created_at', 'status'], true)
            ? $request->query('sort')
            : 'created_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $comments = Comment::query()
            ->with(['author', 'post'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->query('q');
                $q->where(function ($inner) use ($term) {
                    $inner->where('body', 'like', "%{$term}%")
                        ->orWhereHas('author', fn ($a) => $a->where('name', 'like', "%{$term}%"));
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        $counts = Comment::selectRaw('status, count(*) as aantal')
            ->groupBy('status')
            ->pluck('aantal', 'status');

        return view('admin.comments.index', [
            'comments' => $comments,
            'status' => $status,
            'sort' => $sort,
            'direction' => $direction,
            'counts' => $counts,
        ]);
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $this->authorize('moderate', $comment);
        $comment->moderate('approved');

        return back()->with('status', 'Reactie goedgekeurd.');
    }

    public function reject(Comment $comment): RedirectResponse
    {
        $this->authorize('moderate', $comment);
        $comment->moderate('rejected');

        return back()->with('status', 'Reactie afgekeurd.');
    }

    public function spam(Comment $comment): RedirectResponse
    {
        $this->authorize('moderate', $comment);
        $comment->moderate('spam');

        return back()->with('status', 'Reactie gemarkeerd als spam.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        return back()->with('status', 'Reactie verwijderd.');
    }
}
