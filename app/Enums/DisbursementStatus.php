<?php

namespace App\Enums;

enum DisbursementStatus: string
{
    case NOT_STARTED = 'NOT_STARTED';
    case PROCESSING = 'PROCESSING';
    case RELEASED = 'RELEASED';
    case RECEIVED = 'RECEIVED';
    case CONFIRMED = 'CONFIRMED';

    public function label(): string
    {
        return __('status.disbursement.'.$this->value);
    }

    public function badgeType(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'neutral',
            self::PROCESSING => 'info',
            self::RELEASED, self::RECEIVED => 'warning',
            self::CONFIRMED => 'success',
        };
    }

    /**
     * @return list<self>
     */
    public static function flow(): array
    {
        return [
            self::NOT_STARTED,
            self::PROCESSING,
            self::RELEASED,
            self::RECEIVED,
            self::CONFIRMED,
        ];
    }

    public function canTransitionTo(self $to): bool
    {
        $flow = self::flow();
        $current = array_search($this, $flow, true);
        $next = array_search($to, $flow, true);

        return $current !== false && $next !== false && $next === $current + 1;
    }
}
