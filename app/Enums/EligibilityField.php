<?php

namespace App\Enums;

enum EligibilityField: string
{
    case EDUCATION_LEVEL = 'EDUCATION_LEVEL';
    case STUDENT_CATEGORY = 'STUDENT_CATEGORY';
    case DISTRICT = 'DISTRICT';
    case PROVINCE = 'PROVINCE';
    case GENDER = 'GENDER';

    public function label(): string
    {
        return __('enum.eligibility_field.'.$this->value);
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
