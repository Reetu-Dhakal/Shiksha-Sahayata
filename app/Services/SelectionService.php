<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\Decision;
use App\Enums\NotificationType;
use App\Enums\Role;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SelectionService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly AuditLogService $audit,
    ) {}

    /**
     * Applications this committee member may review, for their assigned scholarships.
     *
     * @return Builder<Application>
     */
    public function queueFor(User $committee): Builder
    {
        if (! $committee->hasRole(Role::COMMITTEE)) {
            abort(403, __('workflow.selection.errors.not_committee'));
        }

        return Application::query()
            ->with(['scholarship', 'student.school', 'decision', 'scores.criterion'])
            ->whereIn('status', [
                ApplicationStatus::UNDER_REVIEW->value,
                ApplicationStatus::SELECTED->value,
                ApplicationStatus::WAITLISTED->value,
                ApplicationStatus::REJECTED->value,
            ])
            ->whereHas('scholarship', fn (Builder $scholarship): Builder => $scholarship
                ->whereHas('committeeMembers', fn (Builder $member): Builder => $member
                    ->where('user_id', $committee->id)))
            ->orderBy('submitted_at')
            ->orderBy('id');
    }

    public function isMemberOf(User $committee, Application $application): bool
    {
        return $committee->hasRole(Role::COMMITTEE)
            && $application->scholarship->committeeMembers()
                ->where('user_id', $committee->id)
                ->exists();
    }

    public function canView(User $committee, Application $application): bool
    {
        return $this->isMemberOf($committee, $application)
            && in_array($application->status, [
                ApplicationStatus::UNDER_REVIEW,
                ApplicationStatus::SELECTED,
                ApplicationStatus::WAITLISTED,
                ApplicationStatus::REJECTED,
            ], true);
    }

    public function assertCanView(User $committee, Application $application): void
    {
        if (! $this->canView($committee, $application)) {
            abort(403, __('workflow.selection.errors.not_assigned'));
        }
    }

    public function canScore(User $committee, Application $application): bool
    {
        return $this->isMemberOf($committee, $application)
            && $application->status === ApplicationStatus::UNDER_REVIEW;
    }

    /**
     * @param  array<int|string, mixed>  $scores  criterion id => score
     * @return array<string, mixed>
     */
    public function saveScores(User $committee, Application $application, array $scores): Application
    {
        if (! $this->canScore($committee, $application)) {
            abort(403, __('workflow.selection.errors.scores_forbidden'));
        }

        $application->load('scholarship.criteria');

        DB::transaction(function () use ($application, $committee, $scores): void {
            foreach ($application->scholarship->criteria as $criterion) {
                $key = (string) $criterion->id;
                $raw = $scores[$key] ?? null;

                if ($raw === null || $raw === '') {
                    continue;
                }

                if (! is_numeric($raw) || (float) $raw < 0 || (float) $raw > (float) $criterion->maximum_score) {
                    throw ValidationException::withMessages([
                        'scores.'.$key => __('workflow.selection.errors.score_between', [
                            'criterion' => $criterion->name,
                            'max' => $criterion->maximum_score,
                        ]),
                    ]);
                }

                $application->scores()->updateOrCreate(
                    ['criterion_id' => $criterion->id],
                    [
                        'score' => round((float) $raw, 2),
                        'scored_by_user_id' => $committee->id,
                    ],
                );
            }
        });

        return $application->load('scores.criterion');
    }

    public function decide(User $committee, Application $application, Decision $decision, string $reason): Application
    {
        $this->assertCanView($committee, $application);

        if ($application->status !== ApplicationStatus::UNDER_REVIEW) {
            throw ValidationException::withMessages([
                'decision' => __('workflow.selection.errors.already_decided'),
            ]);
        }

        DB::transaction(function () use ($application, $committee, $decision, $reason): void {
            $application->update(['status' => $decision->applicationStatus()]);

            $application->decision()->updateOrCreate(
                ['application_id' => $application->id],
                [
                    'decision' => $decision,
                    'decided_by_user_id' => $committee->id,
                    'reason' => $reason,
                    'decided_at' => now(),
                ],
            );
        });

        $this->announceDecision($application, $decision, $reason, $committee);

        return $application->fresh()->load(['decision', 'scores.criterion']);
    }

    private function announceDecision(Application $application, Decision $decision, string $reason, User $committee): void
    {
        $params = [
            'scholarship' => $application->scholarship->getRawOriginal('title'),
            'decision' => $decision->value,
            'reason' => $reason,
            'application' => (string) $application->id,
        ];

        $this->notifications->notifyMany(
            [$application->submitted_by_user_id ?? $application->student->user_id],
            NotificationType::SELECTION_DECIDED,
            $params,
            route('applications.show', $application),
        );

        $this->audit->record(
            'selection.decision',
            sprintf(
                'Selection committee recorded %s for application #%d: %s',
                $decision->label(),
                $application->id,
                $reason,
            ),
            $application,
            [],
            ['decision' => $decision->value, 'reason' => $reason],
            $committee,
        );
    }
}
