<?php

namespace App\Enums;

enum AwardStatus: string
{
    case ACTIVE = 'ACTIVE';
    case REVOKED = 'REVOKED';

    public function label(): string
    {
        return __('status.award.'.$this->value);
    }

    public function badgeType(): string
    {
        return $this === self::ACTIVE ? 'success' : 'danger';
    }
}
