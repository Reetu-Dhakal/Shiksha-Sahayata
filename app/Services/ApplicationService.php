<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\Role;
use App\Models\Application;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Scholarship $scholarship, Student $student, User $actor, array $data = []): Application
    {
        $this->assertCanActFor($actor, $student, $scholarship);

        if (! $scholarship->isAcceptingApplications()) {
            throw ValidationException::withMessages([
                'scholarship' => 'Applications are not open for this scholarship.',
            ]);
        }

        $existing = Application::query()
            ->where('scholarship_id', $scholarship->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'scholarship' => 'An application for this student and scholarship already exists.',
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
                'application' => 'This application can no longer be edited.',
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
                'application' => 'This application has already been submitted.',
            ]);
        }

        if (! $application->scholarship->isAcceptingApplications()) {
            throw ValidationException::withMessages([
                'application' => 'The application deadline has passed, so this application cannot be submitted.',
            ]);
        }

        $application->update([
            'status' => ApplicationStatus::SUBMITTED,
            'submitted_at' => now(),
            'submitted_by_user_id' => $actor->id,
            'return_remarks' => null,
        ]);

        return $application;
    }

    public function delete(Application $application, User $actor): void
    {
        $this->assertCanActFor($actor, $application->student, $application->scholarship);

        if ($application->status !== ApplicationStatus::DRAFT) {
            throw ValidationException::withMessages([
                'application' => 'Only draft applications can be withdrawn.',
            ]);
        }

        DB::transaction(fn () => $application->delete());
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
            abort(403, 'You are not allowed to act on this application.');
        }
    }
}
