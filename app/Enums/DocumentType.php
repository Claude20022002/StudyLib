<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentType: string
{
    case Cours = 'cours';
    case Examen = 'examen';
    case Td = 'td';
    case Tp = 'tp';
    // Ressources d'accompagnement, souvent partagées par un enseignant hors de ses propres classes
    case Exercice = 'exercice';
    case Article = 'article';

    public function label(): string
    {
        return match ($this) {
            self::Cours => 'Cours',
            self::Examen => 'Examen',
            self::Td => 'TD',
            self::Tp => 'TP',
            self::Exercice => 'Exercice',
            self::Article => 'Article',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Cours, self::Article => 'primary',
            self::Examen => 'warning',
            self::Td, self::Tp, self::Exercice => 'success',
        };
    }
}
