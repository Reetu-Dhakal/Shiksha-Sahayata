<?php

namespace App\Enums;

enum NotificationType: string
{
    case APPLICATION_SUBMITTED = 'APPLICATION_SUBMITTED';
    case APPLICATION_RETURNED = 'APPLICATION_RETURNED';
    case SELECTION_DECIDED = 'SELECTION_DECIDED';
    case APPEAL_SUBMITTED = 'APPEAL_SUBMITTED';
    case APPEAL_DECIDED = 'APPEAL_DECIDED';
    case AWARD_ISSUED = 'AWARD_ISSUED';
    case AWARD_REVOKED = 'AWARD_REVOKED';
    case DISBURSEMENT_CONFIRMED = 'DISBURSEMENT_CONFIRMED';

    public function title(array $params = []): string
    {
        return __('notification.'.$this->value.'.title', $params);
    }

    public function body(array $params = []): string
    {
        return __('notification.'.$this->value.'.body', $params);
    }

    public function badgeType(): string
    {
        return match ($this) {
            self::APPLICATION_RETURNED, self::AWARD_REVOKED => 'warning',
            self::SELECTION_DECIDED, self::AWARD_ISSUED, self::DISBURSEMENT_CONFIRMED, self::APPEAL_DECIDED => 'success',
            default => 'info',
        };
    }
}
