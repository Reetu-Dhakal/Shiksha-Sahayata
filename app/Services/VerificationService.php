<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\Role;
use App\Enums\VerificationStage;
use App\Enums\VerificationStatus;
use App\Models\Application;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VerificationService
{
    public function stageFor(User $officer): VerificationStage
    {
        return match (true) {
            $officer->hasRole(Role::SCHOOL_OFFICER) => VerificationStage::SCHOOL,
            $officer->hasRole(Role::LOCAL_OFFICER) => VerificationStage::LOCAL,
            default => abort(403, 'Only verification officers can review applications.'),
        };
    }

    /**
     * Applications within this officer's jurisdiction that need verification work.
     *
     * @return Builder<Application>
     */
    public function queueFor(User $officer): Builder
    {
        $stage = $this->stageFor($officer);
        $statuses = $this->queueStatuses($stage);

        return Application::query()
            ->with(['scholarship', 'student.school'])
            ->whereIn('status', $statuses)
            ->where($this->jurisdictionConstraint($officer))
            ->orderBy('submitted_at')
            ->orderBy('id');
    }

    /**
     * @return list<string>
     */
    private function queueStatuses(VerificationStage $stage): array
    {
        if ($stage === VerificationStage::SCHOOL) {
            return [ApplicationStatus::SUBMITTED->value, ApplicationStatus::SCHOOL_VERIFICATION->value];
        }

        return [ApplicationStatus::LOCAL_VERIFICATION->value];
    }

    /**
     * @return array<int, mixed>|callable(Builder<Application>): Builder<Application>
     */
    private function jurisdictionConstraint(User $officer)
    {
        if ($officer->hasRole(Role::SCHOOL_OFFICER)) {
            return fn (Builder $query): Builder => $query->whereHas('student', fn (Builder $student) => $student
                ->where('school_id', $officer->school_id));
        }

        $unit = $officer->localEducationUnit;

        return fn (Builder $query): Builder => $query->whereHas('student', fn (Builder $student) => $student
            ->where('district', $unit?->district)
            ->where('municipality', $unit?->municipality));
    }

    public function canReview(User $officer, Application $application): bool
    {
        $stage = $this->stageFor($officer);
        $student = $application->student;

        if ($stage === VerificationStage::SCHOOL) {
            return $officer->school_id !== null
                && $student->school_id === $officer->school_id
                && in_array($application->status, [ApplicationStatus::SUBMITTED, ApplicationStatus::SCHOOL_VERIFICATION], true);
        }

        $unit = $officer->localEducationUnit;

        return $unit !== null
            && $student->district !== null
            && $student->district === $unit->district
            && $student->municipality === $unit->municipality
            && $application->status === ApplicationStatus::LOCAL_VERIFICATION;
    }

    public function assertCanReview(User $officer, Application $application): void
    {
        if (! $this->canReview($officer, $application)) {
            abort(403, 'This application is outside your jurisdiction or not at your verification stage.');
        }
    }

    /**
     * School stage: pick up a submitted application (SUBMITTED -> SCHOOL_VERIFICATION).
     */
    public function start(User $officer, Application $application): Application
    {
        $this->stageFor($officer);

        if (! $this->inJurisdiction($officer, $application)) {
            abort(403, 'This application is outside your jurisdiction.');
        }

        if ($application->status !== ApplicationStatus::SUBMITTED) {
            throw ValidationException::withMessages([
                'status' => 'Only submitted applications can be taken up for review.',
            ]);
        }

        $application->update(['status' => ApplicationStatus::SCHOOL_VERIFICATION]);

        $this->record($application, VerificationStage::SCHOOL, $officer, VerificationStatus::PENDING, null);

        return $application;
    }

    /**
     * Approve the officer's stage and forward the application.
     */
    public function approve(User $officer, Application $application, ?string $remarks): Application
    {
        $stage = $this->stageFor($officer);
        $this->assertCanReview($officer, $application);

        if ($application->status !== $stage->requiredStatus()) {
            throw ValidationException::withMessages([
                'status' => 'This application is not waiting for your verification stage.',
            ]);
        }

        $application->update(['status' => $stage->approvedStatus()]);

        $this->record($application, $stage, $officer, VerificationStatus::VERIFIED, $remarks);

        return $application;
    }

    /**
     * Send the application back to the applicant for corrections.
     */
    public function returnForCorrection(User $officer, Application $application, string $remarks): Application
    {
        $stage = $this->stageFor($officer);

        if (! $this->inJurisdiction($officer, $application)) {
            abort(403, 'This application is outside your jurisdiction.');
        }

        $allowed = $stage === VerificationStage::SCHOOL
            ? [ApplicationStatus::SUBMITTED, ApplicationStatus::SCHOOL_VERIFICATION]
            : [ApplicationStatus::LOCAL_VERIFICATION];

        if (! in_array($application->status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => 'This application is not waiting for your verification stage.',
            ]);
        }

        $application->update([
            'status' => ApplicationStatus::RETURNED_FOR_CORRECTION,
            'return_remarks' => $remarks,
        ]);

        $this->record($application, $stage, $officer, VerificationStatus::RETURNED, $remarks);

        return $application;
    }

    public function inJurisdiction(User $officer, Application $application): bool
    {
        $stage = $this->stageFor($officer);
        $student = $application->student;

        if ($stage === VerificationStage::SCHOOL) {
            return $officer->school_id !== null && $student->school_id === $officer->school_id;
        }

        $unit = $officer->localEducationUnit;

        return $unit !== null
            && $student->district !== null
            && $student->district === $unit->district
            && $student->municipality === $unit->municipality;
    }

    private function record(Application $application, VerificationStage $stage, User $officer, VerificationStatus $status, ?string $remarks): Verification
    {
        return DB::transaction(function () use ($application, $stage, $officer, $status, $remarks): Verification {
            $verification = Verification::query()->firstOrNew([
                'application_id' => $application->id,
                'stage' => $stage->value,
            ]);

            $verification->fill([
                'officer_user_id' => $officer->id,
                'status' => $status,
                'remarks' => $remarks,
                'decided_at' => $status === VerificationStatus::PENDING ? null : now(),
            ])->save();

            return $verification;
        });
    }
}
