<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\Decision;
use App\Enums\Role;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SelectionService
{
    /**
     * Applications this committee member may review, for their assigned scholarships.
     *
     * @return Builder<Application>
     */
    public function queueFor(User $committee): Builder
    {
        if (! $committee->hasRole(Role::COMMITTEE)) {
            abort(403, 'Only selection committee members can review applications.');
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
            abort(403, 'This application is not available for your selection committee assignment.');
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
            abort(403, 'Scores can only be recorded by an assigned committee member while the application is under review.');
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
                        'scores.'.$key => sprintf(
                            '%s must be between 0 and %s.',
                            $criterion->name,
                            $criterion->maximum_score,
                        ),
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
                'decision' => 'A decision has already been recorded for this application.',
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

        return $application->fresh()->load(['decision', 'scores.criterion']);
    }
}
