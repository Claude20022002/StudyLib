<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Teacher = 'teacher';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Étudiant',
            self::Teacher => 'Enseignant',
            self::Admin => 'Administrateur',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    /**
     * Rôle StudyLib d'un rôle de HESTIM Planner (connexion unique). Un rôle inconnu de Planner
     * (compte de service, par exemple) n'ouvre aucun accès.
     */
    public static function fromPlanner(string $role): ?self
    {
        return match ($role) {
            'etudiant' => self::Student,
            'enseignant' => self::Teacher,
            'admin' => self::Admin,
            default => null,
        };
    }

    /** Les documents d'un enseignant ou de l'administration sont publiés sans modération. */
    public function publishesWithoutModeration(): bool
    {
        return $this === self::Teacher || $this === self::Admin;
    }
}
