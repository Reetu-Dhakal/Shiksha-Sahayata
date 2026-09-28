<?php

namespace App\Enums;

enum AppealStatus: string
{
    case SUBMITTED = 'SUBMITTED';
    case UNDER_REVIEW = 'UNDER_REVIEW';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';

    public function label(): string
    {
        return __('status.appeal.'.$this->value);
    }

    public function badgeType(): string
    {
        return match ($this) {
            self::SUBMITTED, self::UNDER_REVIEW => 'info',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        };
    }
}
