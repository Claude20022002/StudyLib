<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        return $document->status === DocumentStatus::Approved
            || $this->owns($user, $document)
            || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Document $document): bool
    {
        return $this->owns($user, $document) || $user->isAdmin();
    }

    public function delete(User $user, Document $document): bool
    {
        return $this->owns($user, $document) || $user->isAdmin();
    }

    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }

    private function owns(User $user, Document $document): bool
    {
        return $document->user_id === $user->getKey();
    }
}
