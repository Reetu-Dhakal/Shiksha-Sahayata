<?php

namespace App\Enums;

enum Decision: string
{
    case SELECTED = 'SELECTED';
    case WAITLISTED = 'WAITLISTED';
    case REJECTED = 'REJECTED';

    public function label(): string
    {
        return __('status.decision.'.$this->value);
    }

    public function badgeType(): string
    {
        return match ($this) {
            self::SELECTED => 'success',
            self::WAITLISTED => 'info',
            self::REJECTED => 'danger',
        };
    }

    public function applicationStatus(): ApplicationStatus
    {
        return match ($this) {
            self::SELECTED => ApplicationStatus::SELECTED,
            self::WAITLISTED => ApplicationStatus::WAITLISTED,
            self::REJECTED => ApplicationStatus::REJECTED,
        };
    }
}
