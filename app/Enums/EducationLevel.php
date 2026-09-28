<?php

namespace App\Enums;

enum EducationLevel: string
{
    case PRIMARY = 'PRIMARY';
    case SECONDARY = 'SECONDARY';
    case HIGHER_SECONDARY = 'HIGHER_SECONDARY';
    case BACHELOR = 'BACHELOR';
    case MASTER = 'MASTER';

    public function label(): string
    {
        return match ($this) {
            self::PRIMARY => 'Primary (Grade 1–5)',
            self::SECONDARY => 'Secondary (Grade 6–10)',
            self::HIGHER_SECONDARY => 'Higher Secondary (Grade 11–12)',
            self::BACHELOR => "Bachelor's level",
            self::MASTER => "Master's level",
        };
    }
}
