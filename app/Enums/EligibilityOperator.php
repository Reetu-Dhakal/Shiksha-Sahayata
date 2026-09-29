<?php

namespace App\Enums;

enum EligibilityOperator: string
{
    case EQUALS = 'EQUALS';
    case IN = 'IN';

    public function label(): string
    {
        return __('enum.eligibility_operator.'.$this->value);
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
