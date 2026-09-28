<?php

namespace App\Enums;

enum ScholarshipStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    case CLOSED = 'CLOSED';
    case COMPLETED = 'COMPLETED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::CLOSED => 'Closed',
            self::COMPLETED => 'Completed',
        };
    }

    public function badgeType(): string
    {
        return match ($this) {
            self::DRAFT => 'neutral',
            self::PUBLISHED => 'success',
            self::CLOSED => 'warning',
            self::COMPLETED => 'info',
        };
    }

    /**
     * @return list<self>
     */
    public static function selectable(): array
    {
        return [self::DRAFT, self::PUBLISHED, self::CLOSED, self::COMPLETED];
    }
}
