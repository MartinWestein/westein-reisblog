<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    /**
     * Publieke comment-store (F5-90). Auth+verified via route-middleware,
     * ProtectAgainstSpam (honeypot) op de route. Auto-status via Comment::booted()
     * op rol (admin/editor -> approved, rest -> pending). Redirect terug met anker.
     */
    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        abort_if(! $post->isPublished(), 404);

        $validated = $request->validated();

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'body' => $validated['body'],
        ]);

        $message = $comment->isApproved()
            ? 'Je reactie is geplaatst.'
            : 'Bedankt! Je reactie wacht op goedkeuring en verschijnt zodra een beheerder deze goedkeurt.';

        return redirect()
            ->to($post->url().'#reactie-'.$comment->id)
            ->with('comment_success', $message);
    }
}
