<?php

namespace App\Services;

use App\Enums\AppealStatus;
use App\Enums\ApplicationStatus;
use App\Enums\NotificationType;
use App\Enums\Role;
use App\Models\Appeal;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppealService
{
    public function __construct(
        private readonly ApplicationService $applications,
        private readonly NotificationService $notifications,
        private readonly AuditLogService $audit,
    ) {}

    public function canSubmit(User $actor, Application $application): bool
    {
        return $this->applications->canView($application, $actor)
            && $application->status === ApplicationStatus::REJECTED
            && $application->appeal()->doesntExist();
    }

    public function submit(User $actor, Application $application, string $reason): Appeal
    {
        if (! $this->applications->canView($application, $actor)) {
            abort(403, __('workflow.appeal.errors.forbidden'));
        }

        if ($application->status !== ApplicationStatus::REJECTED) {
            throw ValidationException::withMessages([
                'application' => __('workflow.appeal.errors.only_rejected'),
            ]);
        }

        if ($application->appeal()->exists()) {
            throw ValidationException::withMessages([
                'application' => __('workflow.appeal.errors.already_appealed'),
            ]);
        }

        $appeal = DB::transaction(function () use ($actor, $application, $reason): Appeal {
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

        $params = [
            'scholarship' => $application->scholarship->getRawOriginal('title'),
            'student' => $application->student->name,
            'appeal' => (string) $appeal->id,
        ];

        $this->notifications->notifyMany(
            $this->reviewerIds($application),
            NotificationType::APPEAL_SUBMITTED,
            $params,
            route('appeals.index'),
        );

        $this->audit->record(
            'appeal.submit',
            sprintf(
                'Appeal #%d submitted against application #%d: %s',
                $appeal->id,
                $application->id,
                $reason,
            ),
            $appeal,
            [],
            ['application_id' => $application->id, 'reason' => $reason],
            $actor,
        );

        return $appeal;
    }

    /**
     * @return Builder<Appeal>
     */
    public function queueFor(User $reviewer): Builder
    {
        if (! $this->isReviewer($reviewer)) {
            abort(403, __('workflow.appeal.errors.not_reviewer'));
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
            abort(403, __('workflow.appeal.errors.not_assigned'));
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
                'status' => __('workflow.appeal.errors.not_reopenable'),
            ]);
        }

        $application = $appeal->application;

        if ($application->status !== ApplicationStatus::APPEALED) {
            throw ValidationException::withMessages([
                'status' => __('workflow.appeal.errors.not_appealed'),
            ]);
        }

        DB::transaction(function () use ($appeal, $application): void {
            $appeal->update(['status' => AppealStatus::UNDER_REVIEW]);
            $application->update(['status' => ApplicationStatus::UNDER_REVIEW]);
        });

        $this->audit->record(
            'appeal.reopen',
            sprintf('Appeal #%d reopened and application #%d returned to selection review.', $appeal->id, $application->id),
            $appeal,
            ['status' => AppealStatus::SUBMITTED->value],
            ['status' => AppealStatus::UNDER_REVIEW->value],
            $reviewer,
        );

        return $appeal->refresh();
    }

    public function decide(User $reviewer, Appeal $appeal, bool $approved, string $remarks): Appeal
    {
        $this->assertCanReview($reviewer, $appeal);

        if ($appeal->status !== AppealStatus::UNDER_REVIEW) {
            throw ValidationException::withMessages([
                'status' => __('workflow.appeal.errors.reopen_first'),
            ]);
        }

        $application = $appeal->application;

        if ($application->status !== ApplicationStatus::UNDER_REVIEW) {
            throw ValidationException::withMessages([
                'status' => __('workflow.appeal.errors.not_under_review'),
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

        $outcome = $approved ? AppealStatus::APPROVED : AppealStatus::REJECTED;

        $params = [
            'scholarship' => $application->scholarship->getRawOriginal('title'),
            'outcome' => $outcome->value,
            'remarks' => $remarks,
            'appeal' => (string) $appeal->id,
        ];

        $this->notifications->notifyMany(
            [$appeal->appellant_user_id],
            NotificationType::APPEAL_DECIDED,
            $params,
            route('applications.show', $application),
        );

        $this->audit->record(
            'appeal.decide',
            sprintf('Appeal #%d decided as %s: %s', $appeal->id, $outcome->label(), $remarks),
            $appeal,
            ['status' => AppealStatus::UNDER_REVIEW->value],
            ['status' => $outcome->value, 'remarks' => $remarks],
            $reviewer,
        );

        return $appeal->refresh();
    }

    /**
     * Administrators plus the scholarship's assigned committee members.
     *
     * @return list<int>
     */
    private function reviewerIds(Application $application): array
    {
        $adminIds = User::query()->where('role', Role::ADMIN->value)->pluck('id');

        return $adminIds
            ->merge($application->scholarship->committeeMembers()->pluck('user_id'))
            ->unique()
            ->values()
            ->all();
    }
}
