<?php

namespace App\Services\Admin;

use App\Models\Comment;
use App\Models\Newsletter;
use App\Models\Post;
use App\Models\Subscriber;
use Illuminate\Support\Collection;

class ActivityFeed
{
    /**
     * Geef de laatste N activiteiten terug uit alle bronnen, gesorteerd op tijd (nieuwst eerst).
     */
    public function latest(int $limit = 15): Collection
    {
        // Per bron de N nieuwste opvragen (eager-loaded)
        $perSource = max($limit, 10);

        $posts = Post::with('author:id,name')
            ->whereIn('status', ['published', 'draft'])
            ->orderByDesc('updated_at')
            ->limit($perSource)
            ->get()
            ->map(fn (Post $post) => $this->fromPost($post));

        $comments = Comment::with(['author:id,name', 'post:id,title,slug'])
            ->orderByDesc('created_at')
            ->limit($perSource)
            ->get()
            ->map(fn (Comment $comment) => $this->fromComment($comment));

        $subscribers = Subscriber::orderByDesc('created_at')
            ->limit($perSource)
            ->get()
            ->map(fn (Subscriber $subscriber) => $this->fromSubscriber($subscriber));

        $newsletters = Newsletter::with('author:id,name')
            ->whereNotNull('sent_at')
            ->orderByDesc('sent_at')
            ->limit($perSource)
            ->get()
            ->map(fn (Newsletter $newsletter) => $this->fromNewsletter($newsletter));

        return $posts
            ->concat($comments)
            ->concat($subscribers)
            ->concat($newsletters)
            ->sortByDesc('at')
            ->take($limit)
            ->values();
    }

    private function fromPost(Post $post): array
    {
        $isPublish = $post->status === 'published' && $post->published_at;
        $at = $isPublish ? $post->published_at : $post->updated_at;
        $verb = $isPublish ? 'publiceerde' : 'werkte bij';

        return [
            'type' => 'post',
            'icon' => 'bi-journal-text',
            'at' => $at,
            'actor' => $post->author?->name ?? 'Onbekend',
            'verb' => $verb,
            'subject' => $post->title,
            'subject_url' => null, // wordt admin.posts.edit later
        ];
    }

    private function fromComment(Comment $comment): array
    {
        return [
            'type' => 'comment',
            'icon' => 'bi-chat-left-dots',
            'at' => $comment->created_at,
            'actor' => $comment->author?->name ?? 'Anoniem',
            'verb' => 'reageerde op',
            'subject' => $comment->post?->title ?? '(verwijderde post)',
            'subject_url' => null,
            'badge' => $comment->status === 'pending' ? 'Wacht op moderatie' : null,
        ];
    }

    private function fromSubscriber(Subscriber $subscriber): array
    {
        $confirmed = $subscriber->confirmed_at !== null;

        return [
            'type' => 'subscriber',
            'icon' => 'bi-envelope-at',
            'at' => $subscriber->confirmed_at ?? $subscriber->created_at,
            'actor' => $subscriber->email,
            'verb' => $confirmed ? 'bevestigde abonnement' : 'meldde zich aan',
            'subject' => null,
            'subject_url' => null,
        ];
    }

    private function fromNewsletter(Newsletter $newsletter): array
    {
        return [
            'type' => 'newsletter',
            'icon' => 'bi-megaphone',
            'at' => $newsletter->sent_at,
            'actor' => $newsletter->author?->name ?? 'Systeem',
            'verb' => 'verstuurde nieuwsbrief',
            'subject' => $newsletter->subject,
            'subject_url' => null,
        ];
    }
}
