@extends('layouts.app')

@section('title', 'New application')

@section('content')
<div class="mb-6">
    <nav class="text-xs text-slate-500">
        <a href="{{ route('applications.index') }}" class="hover:text-blue-800">{{ $assisted ? __('nav.assisted_applications') : 'My applications' }}</a>
        <span class="mx-1">/</span>
        <span>New application</span>
    </nav>
    <h1 class="mt-2 text-lg font-semibold text-slate-900">{{ $assisted ? 'New assisted application' : 'New scholarship application' }}</h1>
    <p class="mt-1 text-sm text-slate-600">
        {{ $assisted
            ? 'Fill this application for a student in your jurisdiction. It will be marked as an assisted application.'
            : 'Create a draft first — you can review it before submitting.' }}
    </p>
</div>

@if ($scholarships->isEmpty())
    <x-empty-state title="No scholarships are accepting applications" message="Check the public listing for scholarships with open deadlines.">
        <a href="{{ route('scholarships.index') }}" class="text-sm font-medium text-blue-800 hover:underline">Browse scholarships</a>
    </x-empty-state>
@else
    <form method="POST" action="{{ route('applications.store') }}" class="space-y-6">
        @csrf

        <section class="rounded border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-slate-900">Applicant profile</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <div>
                    <p class="text-xs text-slate-500">Student</p>
                    <p class="text-sm font-medium text-slate-900">{{ $student->name }}</p>
                    <p class="text-xs text-slate-500">Scholar Student ID: {{ $student->scholar_student_id }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">School</p>
                    <p class="text-sm font-medium text-slate-900">{{ $student->school?->name ?? 'Not assigned' }}</p>
                    <p class="text-xs text-slate-500">Grade {{ $student->grade }} · {{ $student->student_category?->label() ?? 'No category' }}</p>
                </div>
            </div>

            @if ($siblings->count() > 1)
                <div class="mt-4">
                    <label for="student" class="mb-1 block text-sm font-medium text-slate-700">{{ $assisted ? 'Student' : 'Applying for' }}</label>
                    <select id="student" name="student_id"
                            class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        @foreach ($siblings as $sibling)
                            <option value="{{ $sibling->id }}" @selected($sibling->id === $student->id)>{{ $sibling->name }} ({{ $sibling->scholar_student_id }})</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="student_id" value="{{ $student->id }}">
            @endif
        </section>

        <section class="rounded border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-slate-900">Scholarship</h2>
            @if ($scholarships->count() > 1)
                <div class="mt-3">
                    <label for="scholarship" class="mb-1 block text-sm font-medium text-slate-700">Choose a scholarship</label>
                    <select id="scholarship" name="scholarship_id"
                            class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        @foreach ($scholarships as $option)
                            <option value="{{ $option->id }}" @selected($option->id === $scholarship?->id)>
                                {{ $option->title }} — deadline {{ $option->application_deadline->format('j M Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="scholarship_id" value="{{ $scholarship->id }}">
                <p class="mt-2 text-sm font-medium text-slate-900">{{ $scholarship->title }}</p>
                <p class="text-xs text-slate-500">{{ $scholarship->provider }} · deadline {{ $scholarship->application_deadline->format('j M Y') }}</p>
            @endif
            @error('scholarship_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            @error('scholarship')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </section>

        <section class="rounded border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-slate-900">Application details</h2>
            <div class="mt-4 grid gap-4">
                <div>
                    <label for="statement" class="mb-1 block text-sm font-medium text-slate-700">Personal statement *</label>
                    <textarea id="statement" name="statement" rows="7" required
                              placeholder="Explain your financial situation, academic goals and why this scholarship matters to you."
                              class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('statement') border-red-400 @enderror">{{ old('statement') }}</textarea>
                    <p class="mt-1 text-xs text-slate-500">Minimum 80 characters. This is reviewed by the selection committee.</p>
                    @error('statement')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="previous_school" class="mb-1 block text-sm font-medium text-slate-700">Previous school</label>
                        <input id="previous_school" name="previous_school" type="text" value="{{ old('previous_school') }}"
                               class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('previous_school') border-red-400 @enderror">
                        @error('previous_school')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="current_grade" class="mb-1 block text-sm font-medium text-slate-700">Current grade</label>
                        <input id="current_grade" name="current_grade" type="number" min="1" max="12" value="{{ old('current_grade', $student->grade) }}"
                               class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('current_grade') border-red-400 @enderror">
                        @error('current_grade')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="grade_point_average" class="mb-1 block text-sm font-medium text-slate-700">GPA (0–4)</label>
                        <input id="grade_point_average" name="grade_point_average" type="number" step="0.01" min="0" max="4" value="{{ old('grade_point_average') }}"
                               class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('grade_point_average') border-red-400 @enderror">
                        @error('grade_point_average')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        <div class="flex gap-3">
            <button type="submit" class="rounded bg-blue-800 px-5 py-2 text-sm font-medium text-white hover:bg-blue-900">Save draft</button>
            <a href="{{ route('applications.index') }}" class="rounded border border-slate-300 px-5 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ __('common.cancel') }}</a>
        </div>
    </form>
@endif
@endsection
