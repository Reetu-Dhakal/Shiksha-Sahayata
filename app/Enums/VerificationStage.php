<?php

namespace App\Enums;

enum VerificationStage: string
{
    case SCHOOL = 'SCHOOL';
    case LOCAL = 'LOCAL';

    public function label(): string
    {
        return match ($this) {
            self::SCHOOL => 'School verification',
            self::LOCAL => 'Local education unit verification',
        };
    }

    public function badgeType(): string
    {
        return $this === self::SCHOOL ? 'info' : 'warning';
    }

    /**
     * Application status that must hold before this stage can be decided.
     */
    public function requiredStatus(): ApplicationStatus
    {
        return match ($this) {
            self::SCHOOL => ApplicationStatus::SCHOOL_VERIFICATION,
            self::LOCAL => ApplicationStatus::LOCAL_VERIFICATION,
        };
    }

    /**
     * Status after this stage is approved.
     */
    public function approvedStatus(): ApplicationStatus
    {
        return match ($this) {
            self::SCHOOL => ApplicationStatus::LOCAL_VERIFICATION,
            self::LOCAL => ApplicationStatus::UNDER_REVIEW,
        };
    }
}
