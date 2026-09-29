<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\AwardStatus;
use App\Enums\DisbursementStatus;
use App\Enums\NotificationType;
use App\Models\Application;
use App\Models\Award;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AwardService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly AuditLogService $audit,
    ) {}

    public function issue(Application $application, User $actor): Award
    {
        if ($application->status !== ApplicationStatus::SELECTED) {
            throw ValidationException::withMessages([
                'application' => __('admin.errors.award_not_selected'),
            ]);
        }

        if ($application->award()->exists()) {
            throw ValidationException::withMessages([
                'application' => __('admin.errors.award_already_issued'),
            ]);
        }

        $award = DB::transaction(function () use ($application, $actor): Award {
            $award = Award::query()->create([
                'application_id' => $application->id,
                'award_number' => $this->nextAwardNumber(),
                'verification_code' => strtoupper(Str::random(24)),
                'status' => AwardStatus::ACTIVE,
                'issued_by_user_id' => $actor->id,
                'issued_at' => now(),
                'disbursement_status' => DisbursementStatus::NOT_STARTED,
            ]);

            $application->update(['status' => ApplicationStatus::AWARDED]);

            return $award;
        });

        $this->notifications->notifyMany(
            [$application->submitted_by_user_id ?? $application->student->user_id],
            NotificationType::AWARD_ISSUED,
            [
                'scholarship' => $application->scholarship->getRawOriginal('title'),
                'award' => $award->award_number,
            ],
            route('awards.index'),
        );

        $this->audit->record(
            'award.issue',
            sprintf(
                'Award %s issued for application #%d (%s).',
                $award->award_number,
                $application->id,
                $application->scholarship->getRawOriginal('title'),
            ),
            $award,
            [],
            ['award_number' => $award->award_number, 'application_id' => $application->id],
            $actor,
        );

        return $award;
    }

    public function revoke(Award $award, User $actor, string $reason): Award
    {
        if ($award->status === AwardStatus::REVOKED) {
            throw ValidationException::withMessages([
                'award' => __('admin.errors.award_already_revoked'),
            ]);
        }

        $award->update([
            'status' => AwardStatus::REVOKED,
            'disbursement_status' => DisbursementStatus::NOT_STARTED,
            'disbursement_remarks' => $reason,
            'disbursement_updated_at' => now(),
        ]);

        $application = $award->application;

        $this->notifications->notifyMany(
            [$application->submitted_by_user_id ?? $application->student->user_id],
            NotificationType::AWARD_REVOKED,
            [
                'scholarship' => $application->scholarship->getRawOriginal('title'),
                'award' => $award->award_number,
                'reason' => $reason,
            ],
            route('awards.index'),
        );

        $this->audit->record(
            'award.revoke',
            sprintf('Award %s revoked: %s', $award->award_number, $reason),
            $award,
            ['status' => AwardStatus::ACTIVE->value],
            ['status' => AwardStatus::REVOKED->value, 'reason' => $reason],
            $actor,
        );

        return $award->refresh();
    }

    public function advanceDisbursement(Award $award, DisbursementStatus $status, ?string $remarks): Award
    {
        if ($award->status !== AwardStatus::ACTIVE) {
            throw ValidationException::withMessages([
                'disbursement' => __('admin.errors.disbursement_revoked'),
            ]);
        }

        if (! $award->disbursement_status->canTransitionTo($status)) {
            throw ValidationException::withMessages([
                'disbursement' => __('admin.errors.disbursement_transition', [
                    'from' => $award->disbursement_status->label(),
                    'to' => $status->label(),
                ]),
            ]);
        }

        $previous = $award->disbursement_status;

        DB::transaction(function () use ($award, $status, $remarks): void {
            $award->update([
                'disbursement_status' => $status,
                'disbursement_remarks' => $remarks,
                'disbursement_updated_at' => now(),
            ]);

            if ($status === DisbursementStatus::CONFIRMED) {
                $award->application->update(['status' => ApplicationStatus::DISBURSEMENT_CONFIRMED]);
            }
        });

        $application = $award->application;

        if ($status === DisbursementStatus::CONFIRMED) {
            $this->notifications->notifyMany(
                [$application->submitted_by_user_id ?? $application->student->user_id],
                NotificationType::DISBURSEMENT_CONFIRMED,
                [
                    'scholarship' => $application->scholarship->getRawOriginal('title'),
                    'award' => $award->award_number,
                ],
                route('awards.index'),
            );
        }

        $this->audit->record(
            'award.disbursement',
            sprintf(
                'Disbursement for award %s moved to %s.%s',
                $award->award_number,
                $status->label(),
                $remarks !== null ? ' '.$remarks : '',
            ),
            $award,
            ['disbursement_status' => $previous->value],
            ['disbursement_status' => $status->value],
        );

        return $award->refresh();
    }

    private function nextAwardNumber(): string
    {
        $year = now()->year;
        $prefix = sprintf('SS-AWD-%d-', $year);

        $last = Award::query()
            ->where('award_number', 'like', $prefix.'%')
            ->orderByDesc('award_number')
            ->value('award_number');

        $sequence = $last !== null ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
