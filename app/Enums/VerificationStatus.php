<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case PENDING = 'PENDING';
    case VERIFIED = 'VERIFIED';
    case RETURNED = 'RETURNED';
    case REJECTED = 'REJECTED';

    public function label(): string
    {
        return __('status.verification.'.$this->value);
    }

    public function badgeType(): string
    {
        return match ($this) {
            self::PENDING => 'info',
            self::VERIFIED => 'success',
            self::RETURNED => 'warning',
            self::REJECTED => 'danger',
        };
    }
}
