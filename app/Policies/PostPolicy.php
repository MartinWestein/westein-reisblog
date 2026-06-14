<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('posts.viewAny');
    }

    public function view(User $user, Post $post): bool
    {
        return $user->can('posts.view');
    }

    public function create(User $user): bool
    {
        return $user->can('posts.create');
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->can('posts.update.any')) {
            return true;
        }

        return $user->can('posts.update.own') && $post->user_id === $user->id;
    }

    public function delete(User $user, Post $post): bool
    {
        if ($user->can('posts.delete.any')) {
            return true;
        }

        return $user->can('posts.delete.own') && $post->user_id === $user->id;
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->can('posts.publish');
    }
}
