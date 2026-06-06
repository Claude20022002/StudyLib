<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\InternshipReview;
use App\Models\User;

class InternshipReviewPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, InternshipReview $review): bool
    {
        return $review->user_id === $user->getKey() || $user->isAdmin();
    }

    public function delete(User $user, InternshipReview $review): bool
    {
        return $review->user_id === $user->getKey() || $user->isAdmin();
    }
}
