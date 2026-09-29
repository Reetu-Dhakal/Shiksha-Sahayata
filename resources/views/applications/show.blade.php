@extends('layouts.app')

@section('title', __('application.application'))

@section('content')
@php
    $currentIndex = $application->statusStepIndex();
    $returned = $application->status === \App\Enums\ApplicationStatus::RETURNED_FOR_CORRECTION;
@endphp

<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <nav class="text-xs text-slate-500">
            <a href="{{ route('applications.index') }}" class="hover:text-blue-800">{{ __('application.my_applications') }}</a>
            <span class="mx-1">/</span>
            <span>{{ __('application.application') }}</span>
        </nav>
        <h1 class="mt-2 text-lg font-semibold text-slate-900">{{ $application->scholarship->title }}</h1>
        <p class="mt-1 text-sm text-slate-600">
            {{ __('application.applicant') }} {{ $application->student->name }} · {{ $application->student->scholar_student_id }}
            @if ($application->is_assisted) · <span class="font-medium">{{ __('application.assisted_application') }}</span> @endif
        </p>
        <div class="mt-2">
            <x-status-badge :type="$application->status->badgeType()" :label="$application->status->label()" />
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        @if ($application->isEditable())
            <a href="{{ route('applications.edit', $application) }}" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                {{ __('common.edit') }}
            </a>
            <form method="POST" action="{{ route('applications.submit', $application) }}"
                  onsubmit="return confirm('{{ __('application.show.edit_confirm') }}');">
                @csrf
                @method('PATCH')
                <button type="submit" class="rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">
                    {{ __('application.show.submit') }}
                </button>
            </form>
        @endif
        @if ($application->status === \App\Enums\ApplicationStatus::DRAFT)
            <form method="POST" action="{{ route('applications.destroy', $application) }}"
                  onsubmit="return confirm('{{ __('application.show.withdraw_confirm') }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded border border-red-200 px-4 py-2 text-sm text-red-700 hover:bg-red-50">{{ __('application.show.withdraw') }}</button>
            </form>
        @endif
    </div>
</div>

@if ($returned && $application->return_remarks)
    <div class="mb-4 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <p class="font-medium">{{ __('application.returned_for_correction') }}</p>
        <p class="mt-1">{{ $application->return_remarks }}</p>
        <p class="mt-1 text-xs">{{ __('application.show.returned_note') }}</p>
    </div>
@endif

<section class="rounded border border-slate-200 bg-white p-4">
    <h2 class="text-sm font-semibold text-slate-900">{{ __('common.status') }}</h2>    <ol class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($timeline as $index => $stage)
            <li class="rounded border p-3
                @if ($index < $currentIndex) border-green-200 bg-green-50
                @elseif ($index === $currentIndex) border-blue-300 bg-blue-50
                @else border-slate-200 bg-slate-50 @endif">
                <span class="text-xs font-semibold
                    @if ($index < $currentIndex) text-green-700
                    @elseif ($index === $currentIndex) text-blue-800
                    @else text-slate-500 @endif">
                    {{ __('application.show.step', ['number' => $index + 1]) }} @if ($index < $currentIndex) · {{ __('application.show.done') }} @elseif ($index === $currentIndex) · {{ __('application.show.current') }} @endif
                </span>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $stage->label() }}</p>
            </li>
        @endforeach
    </ol>

    @if ($application->status === \App\Enums\ApplicationStatus::WAITLISTED)
        <p class="mt-3 text-xs text-slate-600">{{ __('application.show.waitlisted_note') }}</p>
    @elseif ($application->status === \App\Enums\ApplicationStatus::REJECTED)
        <p class="mt-3 text-xs text-slate-600">{{ __('application.show.rejected_note') }}</p>
    @elseif ($application->status === \App\Enums\ApplicationStatus::DRAFT)
        <p class="mt-3 text-xs text-slate-600">{{ __('application.show.draft_note', ['date' => format_date($application->scholarship->application_deadline, 'j M Y')]) }}</p>
    @endif
</section>

@if ($application->decision)
    <section class="mt-4 rounded border border-slate-200 bg-white p-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('application.show.selection_decision') }}</h2>
            <x-status-badge :type="$application->decision->decision->badgeType()" :label="$application->decision->decision->label()" />
        </div>
        <p class="mt-2 text-sm leading-relaxed text-slate-700">{{ $application->decision->reason }}</p>
        <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
            <span>
                {{ $application->decision->decidedBy?->name ?: __('application.show.selection_committee') }}
                @if ($application->decision->decided_at) · {{ format_date($application->decision->decided_at, 'j M Y, H:i') }} @endif
            </span>
            @if ($application->scores->isNotEmpty())
                <span>{{ __('application.show.weighted_score') }} <strong class="text-slate-800">{{ number_format($application->weightedTotal(), 2) }}%</strong></span>
            @endif
        </div>
    </section>
@endif

@if ($application->appeal)
    <section class="mt-4 rounded border border-slate-200 bg-white p-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('application.show.appeal') }}</h2>
            <x-status-badge :type="$application->appeal->status->badgeType()" :label="$application->appeal->status->label()" />
        </div>
        <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $application->appeal->reason }}</p>
        @if ($application->appeal->review_remarks)
            <div class="mt-3 rounded border border-slate-200 bg-slate-50 px-3 py-2">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('application.show.appeal_decision') }}</p>
                <p class="mt-1 text-sm text-slate-700">{{ $application->appeal->review_remarks }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $application->appeal->reviewer?->name ?: __('application.show.reviewer') }}
                    @if ($application->appeal->decided_at) · {{ format_date($application->appeal->decided_at, 'j M Y') }} @endif
                </p>
            </div>
        @else
            <p class="mt-2 text-xs text-slate-500">{{ __('application.show.appeal_submitted_note', ['date' => $application->appeal->submitted_at ? format_date($application->appeal->submitted_at, 'j M Y, H:i') : '—']) }}</p>
        @endif
    </section>
@elseif ($application->status === \App\Enums\ApplicationStatus::REJECTED && $application->decision)
    <section class="mt-4 rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('application.show.appeal_this_decision') }}</h2>
        <p class="mt-1 text-xs text-slate-500">
            {{ __('application.show.appeal_intro') }}
        </p>

        <form method="POST" action="{{ route('applications.appeal', $application) }}" class="mt-3">
            @csrf
            @method('PATCH')

            <label for="appeal_reason" class="mb-1 block text-xs font-medium text-slate-600">{{ __('application.show.appeal_reason') }} *</label>
            <textarea id="appeal_reason" name="reason" rows="5" required
                      placeholder="{{ __('application.show.appeal_placeholder') }}"
                      class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">{{ old('reason') }}</textarea>
            @error('reason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            @error('application')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

            <button type="submit" class="mt-3 rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900"
                    onclick="return confirm('{{ __('application.show.appeal_confirm') }}');">
                {{ __('application.show.submit_appeal') }}
            </button>
        </form>
    </section>
@endif

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <section class="rounded border border-slate-200 bg-white p-4 lg:col-span-2">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('application.application_details') }}</h2>
        <dl class="mt-3 space-y-3 text-sm">
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('application.personal_statement') }}</dt>
                <dd class="mt-1 whitespace-pre-line text-slate-700">{{ $application->statement }}</dd>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('application.previous_school') }}</dt>
                    <dd class="mt-1 text-slate-800">{{ $application->previous_school ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('application.current_grade') }}</dt>
                    <dd class="mt-1 text-slate-800">{{ $application->current_grade ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('application.gpa') }}</dt>
                    <dd class="mt-1 text-slate-800">{{ $application->grade_point_average ?? '—' }}</dd>
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('application.show.scholarship_deadline') }}</dt>
                    <dd class="mt-1 text-slate-800">{{ format_date($application->scholarship->application_deadline, 'j M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('application.show.submitted') }}</dt>
                    <dd class="mt-1 text-slate-800">{{ $application->submitted_at ? format_date($application->submitted_at, 'j M Y, H:i') : __('application.show.not_submitted') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('application.show.submitted_by') }}</dt>
                    <dd class="mt-1 text-slate-800">{{ $application->submittedBy?->name ?: '—' }}</dd>
                </div>
            </div>
        </dl>
    </section>

    <aside class="space-y-6">
        <section class="rounded border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('application.applicant_profile') }}</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">{{ __('application.scholar_student_id') }}</dt>
                    <dd class="font-medium text-slate-900">{{ $application->student->scholar_student_id }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">{{ __('application.grade') }}</dt>
                    <dd class="font-medium text-slate-900">{{ $application->student->grade }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">{{ __('application.school') }}</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $application->student->school?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-600">{{ __('application.show.profile_status') }}</dt>
                    <dd>
                        <x-status-badge
                            :type="$application->student->isVerified() ? 'success' : 'warning'"
                            :label="$application->student->isVerified() ? __('application.verified') : __('application.unverified')" />
                    </dd>
                </div>
            </dl>
        </section>

        <section class="rounded border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('application.show.required_documents') }}</h2>
            @if ($application->scholarship->requiredDocuments->isEmpty())
                <p class="mt-2 text-sm text-slate-500">{{ __('application.show.no_documents') }}</p>
            @else
                <ul class="mt-2 space-y-2 text-sm text-slate-700">
                    @foreach ($application->scholarship->requiredDocuments as $document)
                        <li class="flex items-center justify-between gap-2">
                            <span>{{ $document->document_type->label() }}</span>
                            @php $uploaded = $application->documents->where('document_type', $document->document_type->value)->first(); @endphp
                            <x-status-badge :type="$uploaded ? 'success' : 'warning'" :label="$uploaded ? __('application.uploaded') : ($document->is_required ? __('common.required') : __('common.optional'))" />
                        </li>
                    @endforeach
                </ul>
                @if (Route::has('applications.documents.index'))
                    <a href="{{ route('applications.documents.index', $application) }}" class="mt-3 inline-block text-xs font-medium text-blue-800 hover:underline">
                        {{ __('application.documents.manage') }}
                    </a>
                @endif
            @endif
        </section>
    </aside>
</div>
@endsection