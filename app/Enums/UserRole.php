<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Étudiant',
            self::Admin => 'Administrateur',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }
}
