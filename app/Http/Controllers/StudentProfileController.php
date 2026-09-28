<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentProfileRequest;
use App\Models\School;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentProfileController extends Controller
{
    public function __construct(private readonly StudentService $studentService) {}

    public function show(Request $request): View|RedirectResponse
    {
        $student = $request->user()->student;

        if (! $student) {
            return redirect()->route('profile.create');
        }

        return view('profile.show', [
            'student' => $student,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->student) {
            return redirect()->route('profile.show');
        }

        return $this->formView(null, $request);
    }

    public function store(StudentProfileRequest $request): RedirectResponse
    {
        DB::transaction(fn () => $this->studentService->createProfile($request->user(), $request->validated()));

        return redirect()
            ->route('profile.show')
            ->with('status', 'Your student profile has been created. Your Scholar Student ID has been generated.');
    }

    public function edit(Request $request): View|RedirectResponse
    {
        $student = $request->user()->student;

        if (! $student) {
            return redirect()->route('profile.create');
        }

        return $this->formView($student, $request);
    }

    public function update(StudentProfileRequest $request): RedirectResponse
    {
        $student = $request->user()->student;

        abort_unless($student !== null, 404);

        DB::transaction(function () use ($student, $request) {
            $data = $request->validated();

            $student->update($this->studentService->studentAttributes($data));

            if ($student->guardian) {
                $this->studentService->updateGuardian($student->guardian, $data);
            }
        });

        return redirect()
            ->route('profile.show')
            ->with('status', 'Your student profile has been updated.');
    }

    private function formView(?Student $student, Request $request): View
    {
        return view('profile.form', [
            'student' => $student,
            'schools' => School::query()->where('status', School::STATUS_ACTIVE)->orderBy('name')->get(),
        ]);
    }
}
