<?php

namespace App\Enums;

enum EligibilityOperator: string
{
    case EQUALS = 'EQUALS';
    case IN = 'IN';

    public function label(): string
    {
        return match ($this) {
            self::EQUALS => 'is exactly',
            self::IN => 'is one of (comma separated)',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
