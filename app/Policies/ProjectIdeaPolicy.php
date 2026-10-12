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

    /** Idées générées pour le CV d'un étudiant : un enseignant propose les siennes. */
    public function generate(User $user): bool
    {
        return ! $user->isTeacher();
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
