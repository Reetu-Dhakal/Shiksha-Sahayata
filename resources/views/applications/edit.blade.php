@extends('layouts.app')

@section('title', 'Edit application')

@section('content')
<div class="mb-6">
    <nav class="text-xs text-slate-500">
        <a href="{{ route('applications.index') }}" class="hover:text-blue-800">My applications</a>
        <span class="mx-1">/</span>
        <a href="{{ route('applications.show', $application) }}" class="hover:text-blue-800">{{ $application->scholarship->title }}</a>
        <span class="mx-1">/</span>
        <span>Edit</span>
    </nav>
    <h1 class="mt-2 text-lg font-semibold text-slate-900">Edit application</h1>
    <p class="mt-1 text-sm text-slate-600">
        Applying for <span class="font-medium">{{ $application->scholarship->title }}</span>
        · {{ $application->student->name }} ({{ $application->student->scholar_student_id }})
    </p>
</div>

@if ($application->status === \App\Enums\ApplicationStatus::RETURNED_FOR_CORRECTION && $application->return_remarks)
    <div class="mb-4 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <p class="font-medium">Returned for correction</p>
        <p class="mt-1">{{ $application->return_remarks }}</p>
    </div>
@endif

<form method="POST" action="{{ route('applications.update', $application) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Application details</h2>
        <div class="mt-4 grid gap-4">
            <div>
                <label for="statement" class="mb-1 block text-sm font-medium text-slate-700">Personal statement *</label>
                <textarea id="statement" name="statement" rows="7" required
                          class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('statement') border-red-400 @enderror">{{ old('statement', $application->statement) }}</textarea>
                <p class="mt-1 text-xs text-slate-500">Minimum 80 characters. This is reviewed by the selection committee.</p>
                @error('statement')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="previous_school" class="mb-1 block text-sm font-medium text-slate-700">Previous school</label>
                    <input id="previous_school" name="previous_school" type="text" value="{{ old('previous_school', $application->previous_school) }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('previous_school') border-red-400 @enderror">
                    @error('previous_school')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="current_grade" class="mb-1 block text-sm font-medium text-slate-700">Current grade</label>
                    <input id="current_grade" name="current_grade" type="number" min="1" max="12" value="{{ old('current_grade', $application->current_grade) }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('current_grade') border-red-400 @enderror">
                    @error('current_grade')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="grade_point_average" class="mb-1 block text-sm font-medium text-slate-700">GPA (0–4)</label>
                    <input id="grade_point_average" name="grade_point_average" type="number" step="0.01" min="0" max="4" value="{{ old('grade_point_average', $application->grade_point_average) }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('grade_point_average') border-red-400 @enderror">
                    @error('grade_point_average')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </section>

    <div class="flex gap-3">
        <button type="submit" class="rounded bg-blue-800 px-5 py-2 text-sm font-medium text-white hover:bg-blue-900">{{ __('common.save') }}</button>
        <a href="{{ route('applications.show', $application) }}" class="rounded border border-slate-300 px-5 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
