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
            'phone.regex' => 'Please enter a valid phone number (digits, +, - or spaces only).',
        ]);

        Guardian::query()->create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('guardian.profile.show')
            ->with('status', 'Your guardian profile has been created.');
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
            'phone.regex' => 'Please enter a valid phone number (digits, +, - or spaces only).',
        ]);

        $guardian->update($data);

        return redirect()
            ->route('guardian.profile.show')
            ->with('status', 'Your guardian profile has been updated.');
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
                ->withErrors(['scholar_student_id' => 'Please create your guardian profile first.']);
        }

        $data = $request->validate([
            'scholar_student_id' => ['required', 'string', 'max:30'],
        ]);

        $student = Student::query()
            ->where('scholar_student_id', $data['scholar_student_id'])
            ->first();

        if ($student === null) {
            return back()->withErrors(['scholar_student_id' => 'No student profile matches that Scholar Student ID.']);
        }

        if ($student->guardian_id !== null && $student->guardian_id !== $guardian->id) {
            return back()->withErrors(['scholar_student_id' => 'This student profile is already linked to another guardian.']);
        }

        $student->update(['guardian_id' => $guardian->id]);

        return back()->with('status', 'Student profile linked to your guardian account.');
    }
}
