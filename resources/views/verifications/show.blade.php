@extends('layouts.app')

@section('title', 'Verification')

@section('content')
@php
    $requiredStatus = $stage->requiredStatus();
@endphp

<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <nav class="text-xs text-slate-500">
            <a href="{{ route('verifications.index') }}" class="hover:text-blue-800">{{ __('nav.verifications') }}</a>
            <span class="mx-1">/</span>
            <span>{{ $application->student->name }}</span>
        </nav>
        <h1 class="mt-2 text-lg font-semibold text-slate-900">{{ $application->scholarship->title }}</h1>
        <p class="mt-1 text-sm text-slate-600">
            {{ $application->student->name }} · {{ $application->student->scholar_student_id }}
            · {{ $application->student->school?->name ?? 'No school' }}
            @if ($application->is_assisted) · <span class="font-medium">Assisted application</span> @endif
        </p>
        <div class="mt-2 flex flex-wrap gap-2">
            <x-status-badge :type="$application->status->badgeType()" :label="$application->status->label()" />
            <x-status-badge :type="$stage->badgeType()" :label="$stage->label()" />
        </div>
    </div>
</div>

@if ($application->isSubmitted() && $application->submitted_at)
    <p class="mb-4 text-xs text-slate-500">Submitted {{ $application->submitted_at->format('j M Y, H:i') }}</p>
@endif

@if ($canDecide && $application->status === \App\Enums\ApplicationStatus::SUBMITTED && $stage === \App\Enums\VerificationStage::SCHOOL)
    <div class="mb-4 rounded border border-blue-200 bg-blue-50 p-4">
        <p class="text-sm font-medium text-blue-900">This application is waiting to be taken up.</p>
        <form method="POST" action="{{ route('verifications.start', $application) }}" class="mt-3">
            @csrf
            @method('PATCH')
            <button type="submit" class="rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">
                Take up for school verification
            </button>
        </form>
    </div>
@endif

@if ($canDecide && $application->status === $requiredStatus)
    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        <form method="POST" action="{{ route('verifications.approve', $application) }}" class="rounded border border-green-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-green-900">Verify and forward</h2>
            <p class="mt-1 text-xs text-slate-500">
                @if ($stage === \App\Enums\VerificationStage::SCHOOL)
                    Confirms enrollment and documents at school level, then forwards to the local education unit.
                @else
                    Confirms local criteria, then forwards the application to the selection committee.
                @endif
            </p>
            <label for="approve_remarks" class="mb-1 mt-3 block text-xs font-medium text-slate-600">Remarks (optional)</label>
            <textarea id="approve_remarks" name="remarks" rows="3"
                      class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-green-700 focus:outline-none focus:ring-1 focus:ring-green-700">{{ old('remarks') }}</textarea>
            @error('remarks')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            <button type="submit" class="mt-3 rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">
                Approve verification
            </button>
        </form>

        <form method="POST" action="{{ route('verifications.return', $application) }}" class="rounded border border-amber-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-amber-900">Return for correction</h2>
            <p class="mt-1 text-xs text-slate-500">Sends the application back to the applicant with your remarks.</p>
            <label for="return_remarks" class="mb-1 mt-3 block text-xs font-medium text-slate-600">Reason *</label>
            <textarea id="return_remarks" name="remarks" rows="3" required
                      placeholder="Explain exactly what needs to be corrected."
                      class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-amber-700 focus:outline-none focus:ring-1 focus:ring-amber-700">{{ old('remarks') }}</textarea>
            @error('remarks')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            <button type="submit" class="mt-3 rounded border border-amber-500 px-4 py-2 text-sm font-medium text-amber-800 hover:bg-amber-50">
                Return application
            </button>
        </form>
    </div>
@elseif (! $canDecide)
    <div class="mb-6 rounded border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        This application is not waiting for your verification stage right now. You can review its details, but no decision can be recorded.
    </div>
@endif

<div class="grid gap-6 lg:grid-cols-3">
    <section class="rounded border border-slate-200 bg-white p-4 lg:col-span-2">
        <h2 class="text-sm font-semibold text-slate-900">Applicant statement</h2>
        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $application->statement }}</p>

        <div class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-4">
            <div>
                <p class="text-xs text-slate-500">Previous school</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->previous_school ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Current grade</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->current_grade ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">GPA</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->grade_point_average ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Grade range required</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->scholarship->gradeRangeLabel() }}</p>
            </div>
        </div>

        <h2 class="mt-6 text-sm font-semibold text-slate-900">Documents</h2>
        @if ($application->documents->isEmpty())
            <p class="mt-2 text-sm text-slate-500">No documents uploaded.</p>
        @else
            <ul class="mt-2 divide-y divide-slate-100">
                @foreach ($application->documents as $document)
                    <li class="flex items-center justify-between gap-3 py-2 text-sm">
                        <div>
                            <p class="font-medium text-slate-800">{{ $document->document_type }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $document->original_filename }}
                                @if ($document->size_bytes) · {{ number_format($document->size_bytes / 1024, 1) }} KB @endif
                                · uploaded {{ $document->created_at->format('j M Y') }}
                            </p>
                        </div>
                        <a href="{{ route('applications.documents.download', [$application, $document->id]) }}"
                           class="shrink-0 text-xs font-medium text-blue-800 hover:underline">Download</a>
                    </li>
                @endforeach
            </ul>
        @endif

        <h2 class="mt-6 text-sm font-semibold text-slate-900">Verification history</h2>
        @if ($application->verifications->isEmpty())
            <p class="mt-2 text-sm text-slate-500">No verification decisions recorded yet.</p>
        @else
            <ul class="mt-2 space-y-2">
                @foreach ($application->verifications as $record)
                    <li class="rounded border border-slate-200 px-3 py-2 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-medium text-slate-800">{{ $record->stage->label() }}</span>
                            <x-status-badge :type="$record->status->badgeType()" :label="$record->status->label()" />
                        </div>
                        @if ($record->remarks)
                            <p class="mt-1 text-xs text-slate-600">{{ $record->remarks }}</p>
                        @endif
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $record->officer?->name ?: 'System' }}
                            @if ($record->decided_at) · {{ $record->decided_at->format('j M Y, H:i') }} @endif
                        </p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <aside class="space-y-6">
        <section class="rounded border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-slate-900">Student profile</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">Grade</dt>
                    <dd class="font-medium text-slate-900">{{ $application->student->grade }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">Category</dt>
                    <dd class="font-medium text-slate-900">{{ $application->student->student_category?->label() ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">District</dt>
                    <dd class="font-medium text-slate-900">{{ $application->student->district ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">Municipality</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $application->student->municipality ?: '—' }}</dd>
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
            <h2 class="text-sm font-semibold text-slate-900">Scholarship rules</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">Deadline</dt>
                    <dd class="font-medium text-slate-900">{{ $application->scholarship->application_deadline->format('j M Y') }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">Level</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $application->scholarship->education_level?->label() ?? 'Any' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">Grades</dt>
                    <dd class="font-medium text-slate-900">{{ $application->scholarship->gradeRangeLabel() }}</dd>
                </div>
            </dl>
        </section>
    </aside>
</div>
@endsection
