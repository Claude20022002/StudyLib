<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    private const AVATAR_DISK = 'public';

    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        if ($avatar !== null) {
            $data['avatar_path'] = $avatar->store('avatars/'.$user->getKey(), self::AVATAR_DISK);
        }

        unset($data['avatar']);

        return $this->users->update($user, $data);
    }
}
