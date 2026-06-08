<?php

declare(strict_types=1);

namespace App\Enums;

enum StudyLevel: string
{
    case L1 = 'l1';
    case L2 = 'l2';
    case L3 = 'l3';
    case M1 = 'm1';
    case M2 = 'm2';

    public function label(): string
    {
        return strtoupper($this->value);
    }

    /**
     * Convertit une année d'études (1 à 5, telle que stockée sur `users.year_level`)
     * en niveau métier équivalent (l1..m2).
     */
    public static function fromYearLevel(int $yearLevel): self
    {
        return match ($yearLevel) {
            1 => self::L1,
            2 => self::L2,
            3 => self::L3,
            4 => self::M1,
            default => self::M2,
        };
    }

    /**
     * Distance ordinale entre deux niveaux (0 = identique, 1 = adjacent, etc.).
     */
    public function distanceTo(self $other): int
    {
        $order = array_flip(array_column(self::cases(), 'value'));

        return abs($order[$this->value] - $order[$other->value]);
    }
}
