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
        return match ($this) {
            self::EDUCATION_LEVEL => 'Education level',
            self::STUDENT_CATEGORY => 'Student category',
            self::DISTRICT => 'District',
            self::PROVINCE => 'Province',
            self::GENDER => 'Gender',
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
