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
        return __('enum.education_level.'.$this->value);
    }
}
