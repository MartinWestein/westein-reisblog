<?php

namespace App\Policies;

use App\Models\Subscriber;
use App\Models\User;

class SubscriberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('subscribers.manage');
    }

    public function view(User $user, Subscriber $subscriber): bool
    {
        return $user->can('subscribers.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('subscribers.manage');
    }

    public function update(User $user, Subscriber $subscriber): bool
    {
        return $user->can('subscribers.manage');
    }

    public function delete(User $user, Subscriber $subscriber): bool
    {
        return $user->can('subscribers.manage');
    }

    public function sendConfirmation(User $user, Subscriber $subscriber): bool
    {
        return $user->can('subscribers.manage');
    }

    public function import(User $user): bool
    {
        return $user->can('subscribers.manage');
    }

    public function export(User $user): bool
    {
        return $user->can('subscribers.manage');
    }
}
