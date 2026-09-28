<?php

namespace App\Enums;

enum Gender: string
{
    case MALE = 'MALE';
    case FEMALE = 'FEMALE';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
            self::OTHER => 'Other',
        };
    }

    public function labelNp(): string
    {
        return match ($this) {
            self::MALE => 'पुरुष',
            self::FEMALE => 'महिला',
            self::OTHER => 'अन्य',
        };
    }
}
