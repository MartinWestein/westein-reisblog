<?php

namespace App\Policies;

use App\Models\Newsletter;
use App\Models\User;

class NewsletterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('newsletters.manage');
    }

    public function view(User $user, Newsletter $newsletter): bool
    {
        return $user->can('newsletters.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('newsletters.manage');
    }

    public function update(User $user, Newsletter $newsletter): bool
    {
        return $user->can('newsletters.manage') && $newsletter->isEditable();
    }

    public function delete(User $user, Newsletter $newsletter): bool
    {
        return $user->can('newsletters.manage') && $newsletter->isEditable();
    }

    public function sendTest(User $user, Newsletter $newsletter): bool
    {
        return $user->can('newsletters.manage') && $newsletter->isEditable();
    }

    public function dispatch(User $user, Newsletter $newsletter): bool
    {
        return $user->can('newsletters.manage') && $newsletter->isEditable();
    }
}
