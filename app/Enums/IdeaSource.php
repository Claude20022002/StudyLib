<?php

declare(strict_types=1);

namespace App\Enums;

enum IdeaSource: string
{
    case Student = 'student';
    case Ai = 'ai';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Étudiant',
            self::Ai => 'IA',
        };
    }
}
