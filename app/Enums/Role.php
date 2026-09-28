<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case STUDENT = 'student';
    case GUARDIAN = 'guardian';
    case SCHOOL_OFFICER = 'school_officer';
    case LOCAL_OFFICER = 'local_officer';
    case COMMITTEE = 'committee';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::STUDENT => 'Student',
            self::GUARDIAN => 'Guardian',
            self::SCHOOL_OFFICER => 'School Officer',
            self::LOCAL_OFFICER => 'Local Education Officer',
            self::COMMITTEE => 'Selection Committee Member',
        };
    }

    /**
     * Roles handled by the shared student/guardian dashboard.
     *
     * @return list<self>
     */
    public static function applicants(): array
    {
        return [self::STUDENT, self::GUARDIAN];
    }

    public function isApplicant(): bool
    {
        return in_array($this, self::applicants(), true);
    }

    public function isVerificationOfficer(): bool
    {
        return in_array($this, [self::SCHOOL_OFFICER, self::LOCAL_OFFICER], true);
    }
}
