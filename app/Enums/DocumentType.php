<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentType: string
{
    case Cours = 'cours';
    case Examen = 'examen';
    case Td = 'td';
    case Tp = 'tp';

    public function label(): string
    {
        return match ($this) {
            self::Cours => 'Cours',
            self::Examen => 'Examen',
            self::Td => 'TD',
            self::Tp => 'TP',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Cours => 'primary',
            self::Examen => 'warning',
            self::Td, self::Tp => 'success',
        };
    }
}
