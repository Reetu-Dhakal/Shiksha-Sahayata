@extends('layouts.app')

@section('title', 'Application')

@section('content')
@php
    $currentIndex = $application->statusStepIndex();
    $returned = $application->status === \App\Enums\ApplicationStatus::RETURNED_FOR_CORRECTION;
@endphp

<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <nav class="text-xs text-slate-500">
            <a href="{{ route('applications.index') }}" class="hover:text-blue-800">My applications</a>
            <span class="mx-1">/</span>
            <span>Application</span>
        </nav>
        <h1 class="mt-2 text-lg font-semibold text-slate-900">{{ $application->scholarship->title }}</h1>
        <p class="mt-1 text-sm text-slate-600">
            Applicant: {{ $application->student->name }} · {{ $application->student->scholar_student_id }}
            @if ($application->is_assisted) · <span class="font-medium">Assisted application</span> @endif
        </p>
        <div class="mt-2">
            <x-status-badge :type="$application->status->badgeType()" :label="$application->status->label()" />
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        @if ($application->isEditable())
            <a href="{{ route('applications.edit', $application) }}" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                Edit
            </a>
            <form method="POST" action="{{ route('applications.submit', $application) }}"
                  onsubmit="return confirm('Submit this application? It can no longer be edited afterwards unless it is returned for correction.');">
                @csrf
                @method('PATCH')
                <button type="submit" class="rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">
                    Submit application
                </button>
            </form>
        @endif
        @if ($application->status === \App\Enums\ApplicationStatus::DRAFT)
            <form method="POST" action="{{ route('applications.destroy', $application) }}"
                  onsubmit="return confirm('Withdraw this draft application?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded border border-red-200 px-4 py-2 text-sm text-red-700 hover:bg-red-50">Withdraw</button>
            </form>
        @endif
    </div>
</div>

@if ($returned && $application->return_remarks)
    <div class="mb-4 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <p class="font-medium">Returned for correction</p>
        <p class="mt-1">{{ $application->return_remarks }}</p>
        <p class="mt-1 text-xs">Update your application and submit it again before the deadline.</p>
    </div>
@endif

<section class="rounded border border-slate-200 bg-white p-4">
    <h2 class="text-sm font-semibold text-slate-900">Status</h2>
    <ol class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($timeline as $index => $stage)
            <li class="rounded border p-3
                @if ($index < $currentIndex) border-green-200 bg-green-50
                @elseif ($index === $currentIndex) border-blue-300 bg-blue-50
                @else border-slate-200 bg-slate-50 @endif">
                <span class="text-xs font-semibold
                    @if ($index < $currentIndex) text-green-700
                    @elseif ($index === $currentIndex) text-blue-800
                    @else text-slate-500 @endif">
                    Step {{ $index + 1 }} @if ($index < $currentIndex) · done @elseif ($index === $currentIndex) · current @endif
                </span>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $stage->label() }}</p>
            </li>
        @endforeach
    </ol>

    @if ($application->status === \App\Enums\ApplicationStatus::WAITLISTED)
        <p class="mt-3 text-xs text-slate-600">You are on the waiting list. You will be notified if a slot becomes free.</p>
    @elseif ($application->status === \App\Enums\ApplicationStatus::REJECTED)
        <p class="mt-3 text-xs text-slate-600">You can appeal this decision from the appeals section while an appeal window is open.</p>
    @elseif ($application->status === \App\Enums\ApplicationStatus::DRAFT)
        <p class="mt-3 text-xs text-slate-600">This draft has not been submitted yet. Submit it before
            {{ $application->scholarship->application_deadline->format('j M Y') }}.</p>
    @endif
</section>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <section class="rounded border border-slate-200 bg-white p-4 lg:col-span-2">
        <h2 class="text-sm font-semibold text-slate-900">Application details</h2>
        <dl class="mt-3 space-y-3 text-sm">
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">Personal statement</dt>
                <dd class="mt-1 whitespace-pre-line text-slate-700">{{ $application->statement }}</dd>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Previous school</dt>
                    <dd class="mt-1 text-slate-800">{{ $application->previous_school ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Current grade</dt>
                    <dd class="mt-1 text-slate-800">{{ $application->current_grade ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">GPA</dt>
                    <dd class="mt-1 text-slate-800">{{ $application->grade_point_average ?? '—' }}</dd>
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Scholarship deadline</dt>
                    <dd class="mt-1 text-slate-800">{{ $application->scholarship->application_deadline->format('j M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Submitted</dt>
                    <dd class="mt-1 text-slate-800">{{ $application->submitted_at?->format('j M Y, H:i') ?: 'Not submitted' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Submitted by</dt>
                    <dd class="mt-1 text-slate-800">{{ $application->submittedBy?->name ?: '—' }}</dd>
                </div>
            </div>
        </dl>
    </section>

    <aside class="space-y-6">
        <section class="rounded border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-slate-900">Applicant profile</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">Scholar Student ID</dt>
                    <dd class="font-medium text-slate-900">{{ $application->student->scholar_student_id }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">Grade</dt>
                    <dd class="font-medium text-slate-900">{{ $application->student->grade }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">School</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $application->student->school?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">Profile status</dt>
                    <dd>
                        <x-status-badge
                            :type="$application->student->isVerified() ? 'success' : 'warning'"
                            :label="$application->student->isVerified() ? 'Verified' : 'Unverified'" />
                    </dd>
                </div>
            </dl>
        </section>

        <section class="rounded border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-slate-900">Required documents</h2>
            @if ($application->scholarship->requiredDocuments->isEmpty())
                <p class="mt-2 text-sm text-slate-500">This scholarship does not require additional documents.</p>
            @else
                <ul class="mt-2 space-y-2 text-sm text-slate-700">
                    @foreach ($application->scholarship->requiredDocuments as $document)
                        <li class="flex items-center justify-between gap-2">
                            <span>{{ $document->document_type->label() }}</span>
                            @php $uploaded = $application->documents->where('document_type', $document->document_type->value)->first(); @endphp
                            <x-status-badge :type="$uploaded ? 'success' : 'warning'" :label="$uploaded ? 'Uploaded' : ($document->is_required ? 'Required' : 'Optional')" />
                        </li>
                    @endforeach
                </ul>
                @if (Route::has('applications.documents.index'))
                    <a href="{{ route('applications.documents.index', $application) }}" class="mt-3 inline-block text-xs font-medium text-blue-800 hover:underline">
                        Manage documents
                    </a>
                @endif
            @endif
        </section>
    </aside>
</div>
@endsection