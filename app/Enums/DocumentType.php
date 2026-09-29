<?php

namespace App\Enums;

enum DocumentType: string
{
    case BIRTH_REGISTRATION = 'BIRTH_REGISTRATION';
    case SCHOOL_VERIFICATION = 'SCHOOL_VERIFICATION';
    case INCOME_CERTIFICATE = 'INCOME_CERTIFICATE';
    case CASTE_CERTIFICATE = 'CASTE_CERTIFICATE';
    case CITIZENSHIP_GUARDIAN = 'CITIZENSHIP_GUARDIAN';
    case DISABILITY_CERTIFICATE = 'DISABILITY_CERTIFICATE';
    case ACADEMIC_REPORT = 'ACADEMIC_REPORT';
    case PHOTO = 'PHOTO';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return __('enum.document_type.'.$this->value);
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
