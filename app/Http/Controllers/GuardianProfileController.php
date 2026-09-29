<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuardianProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('guardian.show', [
            'guardian' => $user->guardian,
            'students' => Student::query()
                ->where('guardian_id', $user->guardian?->id)
                ->with('school')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'relationship' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'citizenship_number' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
        ], [
            'phone.regex' => __('validation.custom.phone.regex'),
        ]);

        Guardian::query()->create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('guardian.profile.show')
            ->with('status', __('profile.flash.guardian_created'));
    }

    public function update(Request $request): RedirectResponse
    {
        $guardian = $request->user()->guardian;

        abort_unless($guardian !== null, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'relationship' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'citizenship_number' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
        ], [
            'phone.regex' => __('validation.custom.phone.regex'),
        ]);

        $guardian->update($data);

        return redirect()
            ->route('guardian.profile.show')
            ->with('status', __('profile.flash.guardian_updated'));
    }

    /**
     * Link an existing student profile to this guardian using its Scholar Student ID.
     */
    public function linkStudent(Request $request): RedirectResponse
    {
        $guardian = $request->user()->guardian;

        if ($guardian === null) {
            return redirect()
                ->route('guardian.profile.show')
                ->withErrors(['scholar_student_id' => __('profile.errors.guardian_required')]);
        }

        $data = $request->validate([
            'scholar_student_id' => ['required', 'string', 'max:30'],
        ]);

        $student = Student::query()
            ->where('scholar_student_id', $data['scholar_student_id'])
            ->first();

        if ($student === null) {
            return back()->withErrors(['scholar_student_id' => __('profile.errors.student_not_found')]);
        }

        if ($student->guardian_id !== null && $student->guardian_id !== $guardian->id) {
            return back()->withErrors(['scholar_student_id' => __('profile.errors.already_linked')]);
        }

        $student->update(['guardian_id' => $guardian->id]);

        return back()->with('status', __('profile.flash.linked'));
    }
}
