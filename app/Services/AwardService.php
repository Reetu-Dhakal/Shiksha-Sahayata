<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\AwardStatus;
use App\Enums\DisbursementStatus;
use App\Models\Application;
use App\Models\Award;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AwardService
{
    public function issue(Application $application, User $actor): Award
    {
        if ($application->status !== ApplicationStatus::SELECTED) {
            throw ValidationException::withMessages([
                'application' => 'Awards can only be issued for applications with a SELECTED decision.',
            ]);
        }

        if ($application->award()->exists()) {
            throw ValidationException::withMessages([
                'application' => 'An award has already been issued for this application.',
            ]);
        }

        return DB::transaction(function () use ($application, $actor): Award {
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
    }

    public function revoke(Award $award, User $actor, string $reason): Award
    {
        if ($award->status === AwardStatus::REVOKED) {
            throw ValidationException::withMessages([
                'award' => 'This award is already revoked.',
            ]);
        }

        $award->update([
            'status' => AwardStatus::REVOKED,
            'disbursement_status' => DisbursementStatus::NOT_STARTED,
            'disbursement_remarks' => $reason,
            'disbursement_updated_at' => now(),
        ]);

        return $award->refresh();
    }

    public function advanceDisbursement(Award $award, DisbursementStatus $status, ?string $remarks): Award
    {
        if ($award->status !== AwardStatus::ACTIVE) {
            throw ValidationException::withMessages([
                'disbursement' => 'Disbursement cannot be tracked for a revoked award.',
            ]);
        }

        if (! $award->disbursement_status->canTransitionTo($status)) {
            throw ValidationException::withMessages([
                'disbursement' => sprintf(
                    'Disbursement status cannot move from %s to %s.',
                    $award->disbursement_status->label(),
                    $status->label(),
                ),
            ]);
        }

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
