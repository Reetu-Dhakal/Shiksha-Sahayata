@extends('layouts.app')

@section('title', __('workflow.verification.show.title'))

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
            · {{ $application->student->school?->name ?? __('workflow.common.no_school') }}
            @if ($application->is_assisted) · <span class="font-medium">{{ __('workflow.verification.show.assisted_application') }}</span> @endif
        </p>
        <div class="mt-2 flex flex-wrap gap-2">
            <x-status-badge :type="$application->status->badgeType()" :label="$application->status->label()" />
            <x-status-badge :type="$stage->badgeType()" :label="$stage->label()" />
        </div>
    </div>
</div>

@if ($application->isSubmitted() && $application->submitted_at)
    <p class="mb-4 text-xs text-slate-500">{{ __('workflow.common.submitted') }} {{ format_date($application->submitted_at, 'j M Y, H:i') }}</p>
@endif

@if ($canDecide && $application->status === \App\Enums\ApplicationStatus::SUBMITTED && $stage === \App\Enums\VerificationStage::SCHOOL)
    <div class="mb-4 rounded border border-blue-200 bg-blue-50 p-4">
        <p class="text-sm font-medium text-blue-900">{{ __('workflow.verification.show.waiting_notice') }}</p>
        <form method="POST" action="{{ route('verifications.start', $application) }}" class="mt-3">
            @csrf
            @method('PATCH')
            <button type="submit" class="rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">
                {{ __('workflow.verification.show.take_up') }}
            </button>
        </form>
    </div>
@endif

@if ($canDecide && $application->status === $requiredStatus)
    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        <form method="POST" action="{{ route('verifications.approve', $application) }}" class="rounded border border-green-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-green-900">{{ __('workflow.verification.show.verify_forward') }}</h2>
            <p class="mt-1 text-xs text-slate-500">
                @if ($stage === \App\Enums\VerificationStage::SCHOOL)
                    {{ __('workflow.verification.show.school_stage_help') }}
                @else
                    {{ __('workflow.verification.show.local_stage_help') }}
                @endif
            </p>
            <label for="approve_remarks" class="mb-1 mt-3 block text-xs font-medium text-slate-600">{{ __('workflow.verification.show.approve_remarks') }}</label>
            <textarea id="approve_remarks" name="remarks" rows="3"
                      class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-green-700 focus:outline-none focus:ring-1 focus:ring-green-700">{{ old('remarks') }}</textarea>
            @error('remarks')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            <button type="submit" class="mt-3 rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">
                {{ __('workflow.verification.show.approve_submit') }}
            </button>
        </form>

        <form method="POST" action="{{ route('verifications.return', $application) }}" class="rounded border border-amber-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-amber-900">{{ __('workflow.verification.show.return_title') }}</h2>
            <p class="mt-1 text-xs text-slate-500">{{ __('workflow.verification.show.return_help') }}</p>
            <label for="return_remarks" class="mb-1 mt-3 block text-xs font-medium text-slate-600">{{ __('workflow.verification.show.return_reason') }}</label>
            <textarea id="return_remarks" name="remarks" rows="3" required
                      placeholder="{{ __('workflow.verification.show.return_placeholder') }}"
                      class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-amber-700 focus:outline-none focus:ring-1 focus:ring-amber-700">{{ old('remarks') }}</textarea>
            @error('remarks')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            <button type="submit" class="mt-3 rounded border border-amber-500 px-4 py-2 text-sm font-medium text-amber-800 hover:bg-amber-50">
                {{ __('workflow.verification.show.return_submit') }}
            </button>
        </form>
    </div>
@elseif (! $canDecide)
    <div class="mb-6 rounded border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        {{ __('workflow.verification.show.not_waiting') }}
    </div>
@endif

<div class="grid gap-6 lg:grid-cols-3">
    <section class="rounded border border-slate-200 bg-white p-4 lg:col-span-2">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('workflow.common.applicant_statement') }}</h2>
        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $application->statement }}</p>

        <div class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-4">
            <div>
                <p class="text-xs text-slate-500">{{ __('workflow.verification.show.previous_school') }}</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->previous_school ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">{{ __('workflow.common.current_grade') }}</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->current_grade ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">{{ __('workflow.common.gpa') }}</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->grade_point_average ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">{{ __('workflow.verification.show.grade_range_required') }}</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->scholarship->gradeRangeLabel() }}</p>
            </div>
        </div>

        <h2 class="mt-6 text-sm font-semibold text-slate-900">{{ __('workflow.common.documents') }}</h2>
        @if ($application->documents->isEmpty())
            <p class="mt-2 text-sm text-slate-500">{{ __('workflow.common.no_documents') }}</p>
        @else
            <ul class="mt-2 divide-y divide-slate-100">
                @foreach ($application->documents as $document)
                    <li class="flex items-center justify-between gap-3 py-2 text-sm">
                        <div>
                            <p class="font-medium text-slate-800">{{ \App\Enums\DocumentType::from($document->document_type)->label() }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $document->original_filename }}
                                @if ($document->size_bytes) · {{ number_format($document->size_bytes / 1024, 1) }} KB @endif
                                · {{ __('workflow.verification.show.uploaded') }} {{ format_date($document->created_at, 'j M Y') }}
                            </p>
                        </div>
                        <a href="{{ route('applications.documents.download', [$application, $document->id]) }}"
                           class="shrink-0 text-xs font-medium text-blue-800 hover:underline">{{ __('workflow.common.download') }}</a>
                    </li>
                @endforeach
            </ul>
        @endif

        <h2 class="mt-6 text-sm font-semibold text-slate-900">{{ __('workflow.verification.show.verification_history') }}</h2>
        @if ($application->verifications->isEmpty())
            <p class="mt-2 text-sm text-slate-500">{{ __('workflow.verification.show.no_verification_history') }}</p>
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
                            {{ $record->officer?->name ?: __('workflow.verification.show.system') }}
                            @if ($record->decided_at) · {{ format_date($record->decided_at, 'j M Y, H:i') }} @endif
                        </p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <aside class="space-y-6">
        <section class="rounded border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('workflow.verification.show.student_profile') }}</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">{{ __('workflow.common.grade') }}</dt>
                    <dd class="font-medium text-slate-900">{{ $application->student->grade }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">{{ __('workflow.common.category') }}</dt>
                    <dd class="font-medium text-slate-900">{{ $application->student->student_category?->label() ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">{{ __('workflow.common.district') }}</dt>
                    <dd class="font-medium text-slate-900">{{ $application->student->district ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">{{ __('workflow.verification.show.municipality') }}</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $application->student->municipality ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">{{ __('workflow.verification.show.profile_status') }}</dt>
                    <dd>
                        <x-status-badge
                            :type="$application->student->isVerified() ? 'success' : 'warning'"
                            :label="$application->student->isVerified() ? __('workflow.verification.show.verified') : __('workflow.verification.show.unverified')" />
                    </dd>
                </div>
            </dl>
        </section>

        <section class="rounded border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('workflow.verification.show.scholarship_rules') }}</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">{{ __('workflow.verification.show.deadline') }}</dt>
                    <dd class="font-medium text-slate-900">{{ format_date($application->scholarship->application_deadline, 'j M Y') }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">{{ __('workflow.verification.show.level') }}</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $application->scholarship->education_level?->label() ?? __('workflow.verification.show.any') }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">{{ __('workflow.verification.show.grades') }}</dt>
                    <dd class="font-medium text-slate-900">{{ $application->scholarship->gradeRangeLabel() }}</dd>
                </div>
            </dl>
        </section>
    </aside>
</div>
@endsection
