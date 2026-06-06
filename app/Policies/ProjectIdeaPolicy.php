<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProjectIdea;
use App\Models\User;

class ProjectIdeaPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ProjectIdea $idea): bool
    {
        return $idea->user_id === $user->getKey() || $user->isAdmin();
    }

    public function delete(User $user, ProjectIdea $idea): bool
    {
        return $idea->user_id === $user->getKey() || $user->isAdmin();
    }
}
