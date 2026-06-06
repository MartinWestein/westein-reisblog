<?php

namespace App\Policies;

use App\Models\Destination;
use App\Models\User;

class DestinationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('content.manage');
    }

    public function view(User $user, Destination $destination): bool
    {
        return $user->can('content.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('content.manage');
    }

    public function update(User $user, Destination $destination): bool
    {
        return $user->can('content.manage');
    }

    public function delete(User $user, Destination $destination): bool
    {
        return $user->can('content.manage');
    }
}
