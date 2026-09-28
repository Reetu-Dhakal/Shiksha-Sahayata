<?php

namespace App\Services;

use App\Enums\AppealStatus;
use App\Enums\ApplicationStatus;
use App\Enums\Role;
use App\Models\Appeal;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppealService
{
    public function __construct(private readonly ApplicationService $applications) {}

    public function canSubmit(User $actor, Application $application): bool
    {
        return $this->applications->canView($application, $actor)
            && $application->status === ApplicationStatus::REJECTED
            && $application->appeal()->doesntExist();
    }

    public function submit(User $actor, Application $application, string $reason): Appeal
    {
        if (! $this->applications->canView($application, $actor)) {
            abort(403, 'You are not allowed to act on this application.');
        }

        if ($application->status !== ApplicationStatus::REJECTED) {
            throw ValidationException::withMessages([
                'application' => 'Only rejected applications can be appealed.',
            ]);
        }

        if ($application->appeal()->exists()) {
            throw ValidationException::withMessages([
                'application' => 'An appeal has already been submitted for this application.',
            ]);
        }

        return DB::transaction(function () use ($actor, $application, $reason): Appeal {
            $appeal = Appeal::query()->create([
                'application_id' => $application->id,
                'appellant_user_id' => $actor->id,
                'reason' => $reason,
                'status' => AppealStatus::SUBMITTED,
                'submitted_at' => now(),
            ]);

            $application->update(['status' => ApplicationStatus::APPEALED]);

            return $appeal;
        });
    }

    /**
     * @return Builder<Appeal>
     */
    public function queueFor(User $reviewer): Builder
    {
        if (! $this->isReviewer($reviewer)) {
            abort(403, 'Only the selection committee and administrators can review appeals.');
        }

        $query = Appeal::query()
            ->with(['application.scholarship', 'application.student', 'appellant'])
            ->orderBy('submitted_at')
            ->orderBy('id');

        if (! $reviewer->isAdmin()) {
            $query->whereHas('application', fn (Builder $application): Builder => $application
                ->whereHas('scholarship', fn (Builder $scholarship): Builder => $scholarship
                    ->whereHas('committeeMembers', fn (Builder $member): Builder => $member
                        ->where('user_id', $reviewer->id))));
        }

        return $query;
    }

    public function isReviewer(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole(Role::COMMITTEE);
    }

    public function canReview(User $reviewer, Appeal $appeal): bool
    {
        if (! $this->isReviewer($reviewer)) {
            return false;
        }

        if ($reviewer->isAdmin()) {
            return true;
        }

        return $appeal->application->scholarship->committeeMembers()
            ->where('user_id', $reviewer->id)
            ->exists();
    }

    public function assertCanReview(User $reviewer, Appeal $appeal): void
    {
        if (! $this->canReview($reviewer, $appeal)) {
            abort(403, 'This appeal is not assigned to you.');
        }
    }

    /**
     * Take the application back into the selection round (APPEALED -> UNDER_REVIEW).
     */
    public function reopen(User $reviewer, Appeal $appeal): Appeal
    {
        $this->assertCanReview($reviewer, $appeal);

        if ($appeal->status !== AppealStatus::SUBMITTED) {
            throw ValidationException::withMessages([
                'status' => 'This appeal is not waiting to be reopened.',
            ]);
        }

        $application = $appeal->application;

        if ($application->status !== ApplicationStatus::APPEALED) {
            throw ValidationException::withMessages([
                'status' => 'The application is not in the appealed state.',
            ]);
        }

        DB::transaction(function () use ($appeal, $application): void {
            $appeal->update(['status' => AppealStatus::UNDER_REVIEW]);
            $application->update(['status' => ApplicationStatus::UNDER_REVIEW]);
        });

        return $appeal->refresh();
    }

    public function decide(User $reviewer, Appeal $appeal, bool $approved, string $remarks): Appeal
    {
        $this->assertCanReview($reviewer, $appeal);

        if ($appeal->status !== AppealStatus::UNDER_REVIEW) {
            throw ValidationException::withMessages([
                'status' => 'Reopen the appeal before recording a decision.',
            ]);
        }

        $application = $appeal->application;

        if ($application->status !== ApplicationStatus::UNDER_REVIEW) {
            throw ValidationException::withMessages([
                'status' => 'The application must be back under review first.',
            ]);
        }

        DB::transaction(function () use ($appeal, $reviewer, $approved, $remarks, $application): void {
            $appeal->update([
                'status' => $approved ? AppealStatus::APPROVED : AppealStatus::REJECTED,
                'review_remarks' => $remarks,
                'reviewed_by_user_id' => $reviewer->id,
                'decided_at' => now(),
            ]);

            if (! $approved) {
                $application->update(['status' => ApplicationStatus::REJECTED]);
            }
        });

        return $appeal->refresh();
    }
}
