<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case SCHOOL_VERIFICATION = 'SCHOOL_VERIFICATION';
    case LOCAL_VERIFICATION = 'LOCAL_VERIFICATION';
    case UNDER_REVIEW = 'UNDER_REVIEW';
    case SELECTED = 'SELECTED';
    case WAITLISTED = 'WAITLISTED';
    case REJECTED = 'REJECTED';
    case RETURNED_FOR_CORRECTION = 'RETURNED_FOR_CORRECTION';
    case APPEALED = 'APPEALED';
    case AWARDED = 'AWARDED';
    case DISBURSEMENT_CONFIRMED = 'DISBURSEMENT_CONFIRMED';

    public function label(): string
    {
        return __('status.application.'.$this->value);
    }

    public function badgeType(): string
    {
        return match ($this) {
            self::DRAFT, self::RETURNED_FOR_CORRECTION => 'neutral',
            self::SUBMITTED, self::SCHOOL_VERIFICATION, self::LOCAL_VERIFICATION,
            self::UNDER_REVIEW, self::APPEALED, self::WAITLISTED => 'info',
            self::SELECTED, self::AWARDED, self::DISBURSEMENT_CONFIRMED => 'success',
            self::REJECTED => 'danger',
        };
    }

    /**
     * Single source of truth for allowed application transitions.
     *
     * @return array<string, list<string>>
     */
    public static function transitions(): array
    {
        return [
            'DRAFT' => ['SUBMITTED'],
            'SUBMITTED' => ['SCHOOL_VERIFICATION', 'RETURNED_FOR_CORRECTION'],
            'SCHOOL_VERIFICATION' => ['LOCAL_VERIFICATION', 'RETURNED_FOR_CORRECTION'],
            'LOCAL_VERIFICATION' => ['UNDER_REVIEW', 'RETURNED_FOR_CORRECTION'],
            'UNDER_REVIEW' => ['SELECTED', 'WAITLISTED', 'REJECTED'],
            'WAITLISTED' => ['SELECTED', 'REJECTED'],
            'SELECTED' => ['AWARDED'],
            'REJECTED' => ['APPEALED'],
            'APPEALED' => ['UNDER_REVIEW'],
            'RETURNED_FOR_CORRECTION' => ['SUBMITTED'],
            'AWARDED' => ['DISBURSEMENT_CONFIRMED'],
            'DISBURSEMENT_CONFIRMED' => [],
        ];
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to->value, self::transitions()[$this->value] ?? [], true);
    }

    public function isEditable(): bool
    {
        return $this === self::DRAFT || $this === self::RETURNED_FOR_CORRECTION;
    }

    public function isTerminal(): bool
    {
        return (self::transitions()[$this->value] ?? []) === [];
    }

    /**
     * Ordered stages shown in the applicant status timeline.
     *
     * @return list<self>
     */
    public static function timeline(): array
    {
        return [
            self::SUBMITTED,
            self::SCHOOL_VERIFICATION,
            self::LOCAL_VERIFICATION,
            self::UNDER_REVIEW,
            self::SELECTED,
            self::AWARDED,
            self::DISBURSEMENT_CONFIRMED,
        ];
    }
}
