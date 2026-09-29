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
        return __('enum.student_category.'.$this->value);
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
