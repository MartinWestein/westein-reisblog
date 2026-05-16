<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Newsletter;
use App\Models\Post;
use App\Models\Subscriber;
use App\Services\Admin\ActivityFeed;

class DashboardController extends Controller
{
    public function __invoke(ActivityFeed $activityFeed)
    {
        $weekAgo = now()->subWeek();

        $stats = [
            'posts_published' => [
                'value' => Post::where('status', 'published')->count(),
                'delta' => Post::where('status', 'published')
                    ->where('published_at', '>=', $weekAgo)
                    ->count(),
                'route' => 'admin.posts.index',
            ],
            'drafts' => [
                'value' => Post::where('status', 'draft')->count(),
                'delta' => Post::where('status', 'draft')
                    ->where('updated_at', '>=', $weekAgo)
                    ->count(),
                'route' => 'admin.posts.index',
            ],
            'comments_total' => [
                'value' => Comment::count(),
                'delta' => Comment::where('created_at', '>=', $weekAgo)->count(),
                'route' => 'admin.comments.index',
            ],
            'comments_pending' => [
                'value' => Comment::where('status', 'pending')->count(),
                'delta' => Comment::where('status', 'pending')
                    ->where('created_at', '>=', $weekAgo)
                    ->count(),
                'route' => 'admin.comments.index',
            ],
            'subscribers' => [
                'value' => Subscriber::whereNotNull('confirmed_at')->count(),
                'delta' => Subscriber::whereNotNull('confirmed_at')
                    ->where('confirmed_at', '>=', $weekAgo)
                    ->count(),
                'route' => 'admin.subscribers.index',
            ],
            'newsletters_scheduled' => [
                'value' => Newsletter::whereNotNull('scheduled_at')
                    ->whereNull('sent_at')
                    ->where('scheduled_at', '>', now())
                    ->count(),
                'delta' => null, // 'scheduled' is een toekomstig getal, geen delta
                'route' => 'admin.newsletters.index',
            ],
        ];

        $activities = $activityFeed->latest(15);

        return view('admin.dashboard', compact('stats', 'activities'));
    }
}
