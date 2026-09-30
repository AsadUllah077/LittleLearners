<?php

namespace App\Policies;

use App\Models\ChildProfile;
use App\Models\User;

class ChildProfilePolicy
{
    public function view(User $user, ChildProfile $child): bool
    {
        return $user->is_active && (int) $user->id === (int) $child->user_id;
    }

    public function update(User $user, ChildProfile $child): bool
    {
        return $this->view($user, $child);
    }

    public function delete(User $user, ChildProfile $child): bool
    {
        return $this->view($user, $child);
    }
}
