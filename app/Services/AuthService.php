<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * Inscrit un nouvel étudiant HESTIM.
     *
     * @param  array{name: string, email: string, password: string, filiere_id?: string|null, year_level?: int|null}  $data
     */
    public function register(array $data): User
    {
        return $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'filiere_id' => $data['filiere_id'] ?? null,
            'year_level' => $data['year_level'] ?? null,
            'role' => UserRole::Student->value,
        ]);
    }

    public function emailBelongsToHestim(string $email): bool
    {
        return str_ends_with(strtolower($email), '@hestim.ma');
    }
}
