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
        $studentIds = $this->studentIdsFor($user);

        return view('applications.index', [
            'applications' => Application::query()
                ->with(['scholarship', 'student'])
                ->whereIn('student_id', $studentIds)
                ->orderByDesc('created_at')
                ->paginate(10)
                ->withQueryString(),
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
            ]);
        }

        return view('applications.create', [
            'student' => $student,
            'scholarships' => $scholarships,
            'scholarship' => $scholarship,
            'siblings' => $this->studentsFor($user),
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

        $application->load(['scholarship.criteria', 'student', 'documents']);

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

        return collect();
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

        abort(403, 'Only students and guardians can submit applications.');
    }
}
