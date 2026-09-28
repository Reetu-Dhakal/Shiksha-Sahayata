<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\Role;
use App\Http\Requests\ApplicationRequest;
use App\Models\Application;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\User;
use App\Services\ApplicationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(private readonly ApplicationService $applications) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $assisted = ! $user->isApplicant();

        $query = Application::query()
            ->with(['scholarship', 'student'])
            ->orderByDesc('created_at');

        if ($assisted) {
            $query->where('is_assisted', true)
                ->whereHas('student', fn (Builder $student) => $this->scopeStudents($student, $user));
        } else {
            $query->whereIn('student_id', $this->studentIdsFor($user));
        }

        return view('applications.index', [
            'applications' => $query->paginate(10)->withQueryString(),
            'assisted' => $assisted,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        $student = $this->resolveStudent($user, $request->integer('student', 0) ?: null);
        if ($student instanceof RedirectResponse) {
            return $student;
        }

        $scholarships = Scholarship::query()
            ->where('status', 'PUBLISHED')
            ->orderBy('application_deadline')
            ->get()
            ->filter(fn (Scholarship $item): bool => $item->isAcceptingApplications())
            ->values();

        $scholarship = $request->filled('scholarship')
            ? $scholarships->firstWhere('id', $request->integer('scholarship'))
            : null;

        $scholarship ??= $scholarships->first();

        if ($scholarships->isEmpty()) {
            return view('applications.create', [
                'student' => $student,
                'scholarships' => $scholarships,
                'scholarship' => null,
                'siblings' => $this->studentsFor($user),
                'assisted' => ! $user->isApplicant(),
            ]);
        }

        return view('applications.create', [
            'student' => $student,
            'scholarships' => $scholarships,
            'scholarship' => $scholarship,
            'siblings' => $this->studentsFor($user),
            'assisted' => ! $user->isApplicant(),
        ]);
    }

    public function store(ApplicationRequest $request): RedirectResponse
    {
        $user = $request->user();

        $student = $this->resolveStudent($user, $request->integer('student_id', 0) ?: null);
        if ($student instanceof RedirectResponse) {
            return $student;
        }

        $scholarship = Scholarship::query()->findOrFail($request->input('scholarship_id'));

        $application = $this->applications->create($scholarship, $student, $user, $request->safe()->only([
            'statement',
            'previous_school',
            'current_grade',
            'grade_point_average',
        ]));

        return redirect()
            ->route('applications.show', $application)
            ->with('status', 'Draft application created. Review it and submit before the deadline.');
    }

    public function show(Request $request, Application $application): View
    {
        $this->applications->assertCanActFor($request->user(), $application->student, $application->scholarship);

        $application->load(['scholarship.criteria', 'student', 'documents', 'decision.decidedBy', 'scores.criterion', 'appeal.reviewer']);

        return view('applications.show', [
            'application' => $application,
            'timeline' => ApplicationStatus::timeline(),
        ]);
    }

    public function edit(Request $request, Application $application): View|RedirectResponse
    {
        $this->applications->assertCanActFor($request->user(), $application->student, $application->scholarship);

        if (! $application->isEditable()) {
            return redirect()
                ->route('applications.show', $application)
                ->withErrors(['application' => 'This application can no longer be edited.']);
        }

        return view('applications.edit', [
            'application' => $application,
            'siblings' => $this->studentsFor($request->user()),
        ]);
    }

    public function update(ApplicationRequest $request, Application $application): RedirectResponse
    {
        $this->applications->update($application, $request->user(), $request->safe()->only([
            'statement',
            'previous_school',
            'current_grade',
            'grade_point_average',
        ]));

        return redirect()
            ->route('applications.show', $application)
            ->with('status', 'Application updated.');
    }

    public function submit(Request $request, Application $application): RedirectResponse
    {
        $this->applications->submit($application, $request->user());

        return redirect()
            ->route('applications.show', $application)
            ->with('status', 'Application submitted. You can track its progress here.');
    }

    public function destroy(Request $request, Application $application): RedirectResponse
    {
        $this->applications->delete($application, $request->user());

        return redirect()
            ->route('applications.index')
            ->with('status', 'Draft application withdrawn.');
    }

    /**
     * @return list<int>
     */
    private function studentIdsFor(User $user): array
    {
        if ($user->hasRole(Role::STUDENT)) {
            return $user->student ? [$user->student->id] : [];
        }

        if ($user->hasRole(Role::GUARDIAN)) {
            return $this->studentsFor($user)->pluck('id')->all();
        }

        if ($user->isAdmin()) {
            return Student::query()->pluck('id')->all();
        }

        return [];
    }

    /**
     * Students this account may fill applications for.
     *
     * @return Collection<int, Student>
     */
    private function studentsFor(User $user): Collection
    {
        if ($user->hasRole(Role::GUARDIAN)) {
            return Student::query()
                ->where('guardian_id', $user->guardian?->id)
                ->orderBy('name')
                ->get();
        }

        if ($user->hasRole(Role::STUDENT) && $user->student) {
            return collect([$user->student]);
        }

        if ($user->isAdmin()) {
            return Student::query()->orderBy('name')->get();
        }

        if ($user->hasRole(Role::SCHOOL_OFFICER)) {
            return Student::query()
                ->where('school_id', $user->school_id)
                ->orderBy('name')
                ->get();
        }

        if ($user->hasRole(Role::LOCAL_OFFICER)) {
            $unit = $user->localEducationUnit;

            return Student::query()
                ->where('district', $unit?->district)
                ->where('municipality', $unit?->municipality)
                ->orderBy('name')
                ->get();
        }

        return collect();
    }

    /**
     * Restrict a student query to the officer's jurisdiction.
     */
    private function scopeStudents(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->hasRole(Role::SCHOOL_OFFICER)) {
            return $query->where('school_id', $user->school_id);
        }

        $unit = $user->localEducationUnit;

        return $query
            ->where('district', $unit?->district)
            ->where('municipality', $unit?->municipality);
    }

    private function resolveStudent(User $user, ?int $requestedId): Student|RedirectResponse
    {
        if ($user->hasRole(Role::STUDENT)) {
            $student = $user->student;

            if ($student === null) {
                return redirect()
                    ->route('profile.create')
                    ->with('status', 'Create your student profile before applying for a scholarship.');
            }

            return $student;
        }

        if ($user->hasRole(Role::GUARDIAN)) {
            $students = $this->studentsFor($user);

            if ($students->isEmpty()) {
                return redirect()
                    ->route('guardian.profile.show')
                    ->with('status', 'Link a student profile before applying on their behalf.');
            }

            $student = $requestedId !== null
                ? $students->firstWhere('id', $requestedId)
                : $students->first();

            if ($student === null) {
                return redirect()
                    ->route('guardian.profile.show')
                    ->withErrors(['student_id' => 'Select one of your linked student profiles.']);
            }

            return $student;
        }

        if ($user->isApplicant()) {
            abort(403, 'Only students and guardians can submit applications.');
        }

        $students = $this->studentsFor($user);

        if ($students->isEmpty()) {
            return redirect()
                ->route('applications.index')
                ->withErrors(['student_id' => 'No student profiles are available in your jurisdiction.']);
        }

        $student = $requestedId !== null
            ? $students->firstWhere('id', $requestedId)
            : $students->first();

        if ($student === null) {
            return redirect()
                ->route('applications.index')
                ->withErrors(['student_id' => 'Select a student profile within your jurisdiction.']);
        }

        return $student;
    }
}
