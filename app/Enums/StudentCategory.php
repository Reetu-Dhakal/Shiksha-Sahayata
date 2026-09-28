<?php

namespace App\Enums;

enum StudentCategory: string
{
    case GENERAL = 'GENERAL';
    case DALIT = 'DALIT';
    case JANAJATI = 'JANAJATI';
    case MADHESI = 'MADHESI';
    case THARU = 'THARU';
    case DISABILITY = 'DISABILITY';
    case SINGLE_PARENT = 'SINGLE_PARENT';
    case LOW_INCOME = 'LOW_INCOME';
    case REMOTE_AREA = 'REMOTE_AREA';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => 'General',
            self::DALIT => 'Dalit',
            self::JANAJATI => 'Janajati',
            self::MADHESI => 'Madhesi',
            self::THARU => 'Tharu',
            self::DISABILITY => 'Students with disability',
            self::SINGLE_PARENT => 'Single parent household',
            self::LOW_INCOME => 'Low income household',
            self::REMOTE_AREA => 'Remote / rural area',
            self::OTHER => 'Other',
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
