<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\NotificationType;
use App\Enums\Role;
use App\Models\Application;
use App\Models\RequiredDocument;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly AuditLogService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Scholarship $scholarship, Student $student, User $actor, array $data = []): Application
    {
        $this->assertCanActFor($actor, $student, $scholarship);

        if (! $scholarship->isAcceptingApplications()) {
            throw ValidationException::withMessages([
                'scholarship' => __('application.errors.not_open'),
            ]);
        }

        $existing = Application::query()
            ->where('scholarship_id', $scholarship->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'scholarship' => __('application.errors.duplicate'),
            ]);
        }

        return Application::query()->create([
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
            'submitted_by_user_id' => $actor->id,
            'status' => ApplicationStatus::DRAFT,
            'is_assisted' => in_array($actor->role->value, [
                Role::ADMIN->value,
                Role::SCHOOL_OFFICER->value,
                Role::LOCAL_OFFICER->value,
                Role::COMMITTEE->value,
            ], true),
            'statement' => $data['statement'] ?? null,
            'previous_school' => $data['previous_school'] ?? null,
            'current_grade' => $data['current_grade'] ?? null,
            'grade_point_average' => $data['grade_point_average'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Application $application, User $actor, array $data): Application
    {
        $this->assertCanActFor($actor, $application->student, $application->scholarship);

        if (! $application->isEditable()) {
            throw ValidationException::withMessages([
                'application' => __('application.errors.not_editable'),
            ]);
        }

        $application->fill($data);
        $application->save();

        return $application;
    }

    public function submit(Application $application, User $actor): Application
    {
        $this->assertCanActFor($actor, $application->student, $application->scholarship);

        if (! $application->isEditable()) {
            throw ValidationException::withMessages([
                'application' => __('application.errors.already_submitted'),
            ]);
        }

        if (! $application->scholarship->isAcceptingApplications()) {
            throw ValidationException::withMessages([
                'application' => __('application.errors.deadline_passed'),
            ]);
        }

        $missing = $application->scholarship->requiredDocuments
            ->filter(fn (RequiredDocument $document): bool => $document->is_required)
            ->reject(fn (RequiredDocument $document): bool => $application->documents()
                ->where('document_type', $document->document_type->value)
                ->exists())
            ->map(fn (RequiredDocument $document): string => $document->document_type->label());

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'documents' => __('application.errors.missing_documents', ['list' => $missing->implode(', ')]),
            ]);
        }

        $application->update([
            'status' => ApplicationStatus::SUBMITTED,
            'submitted_at' => now(),
            'submitted_by_user_id' => $actor->id,
            'return_remarks' => null,
        ]);

        $params = [
            'scholarship' => $application->scholarship->getRawOriginal('title'),
            'student' => $application->student->name,
            'application' => (string) $application->id,
        ];

        $this->notifications->notifyMany(
            $this->schoolOfficerIds($application),
            NotificationType::APPLICATION_SUBMITTED,
            $params,
            route('verifications.index'),
        );

        $this->audit->record(
            'application.submit',
            sprintf(
                'Application #%d for "%s" submitted by %s.',
                $application->id,
                $application->scholarship->getRawOriginal('title'),
                $actor->email,
            ),
            $application,
            [],
            ['status' => ApplicationStatus::SUBMITTED->value],
            $actor,
        );

        return $application;
    }

    public function delete(Application $application, User $actor): void
    {
        $this->assertCanActFor($actor, $application->student, $application->scholarship);

        if ($application->status !== ApplicationStatus::DRAFT) {
            throw ValidationException::withMessages([
                'application' => __('application.errors.only_draft'),
            ]);
        }

        DB::transaction(function () use ($application): void {
            $application->delete();
            Storage::disk('local')->deleteDirectory('applications/'.$application->id);
        });
    }

    public function canView(Application $application, User $actor): bool
    {
        return $this->canActFor($actor, $application->student, $application->scholarship);
    }

    public function canActFor(User $actor, Student $student, Scholarship $scholarship): bool
    {
        if ($actor->isAdmin()) {
            return true;
        }

        if ($actor->hasRole(Role::STUDENT)) {
            return $student->user_id === $actor->id;
        }

        if ($actor->hasRole(Role::GUARDIAN)) {
            return $student->guardian_id !== null
                && $actor->guardian?->id === $student->guardian_id;
        }

        if ($actor->hasRole(Role::SCHOOL_OFFICER)) {
            return $actor->school_id !== null && $actor->school_id === $student->school_id;
        }

        if ($actor->hasRole(Role::LOCAL_OFFICER)) {
            $unit = $actor->localEducationUnit;

            return $unit !== null
                && $student->district !== null
                && $student->district === $unit->district
                && $student->municipality === $unit->municipality;
        }

        if ($actor->hasRole(Role::COMMITTEE)) {
            return $scholarship->committeeMembers()->where('user_id', $actor->id)->exists();
        }

        return false;
    }

    public function assertCanActFor(User $actor, Student $student, Scholarship $scholarship): void
    {
        if (! $this->canActFor($actor, $student, $scholarship)) {
            abort(403, __('application.errors.forbidden'));
        }
    }

    /**
     * School officers of the applicant's school, falling back to admins when a school has no officer yet.
     *
     * @return list<int>
     */
    private function schoolOfficerIds(Application $application): array
    {
        $officerIds = User::query()
            ->where('role', Role::SCHOOL_OFFICER->value)
            ->where('school_id', $application->student->school_id)
            ->pluck('id')
            ->all();

        if ($officerIds !== []) {
            return $officerIds;
        }

        return User::query()->where('role', Role::ADMIN->value)->pluck('id')->all();
    }
}
