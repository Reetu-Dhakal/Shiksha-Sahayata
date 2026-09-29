@extends('layouts.app')

@section('title', __('workflow.appeal.show.title'))

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <nav class="text-xs text-slate-500">
            <a href="{{ route('appeals.index') }}" class="hover:text-blue-800">{{ __('nav.appeals') }}</a>
            <span class="mx-1">/</span>
            <span>{{ $appeal->application->student->name }}</span>
        </nav>
        <h1 class="mt-2 text-lg font-semibold text-slate-900">{{ $appeal->application->scholarship->title }}</h1>
        <p class="mt-1 text-sm text-slate-600">
            {{ __('workflow.appeal.show.appeal_by', ['name' => $appeal->appellant?->name ?: __('workflow.appeal.show.applicant')]) }}
            · {{ $appeal->application->student->name }} ({{ $appeal->application->student->scholar_student_id }})
        </p>
        <div class="mt-2 flex flex-wrap gap-2">
            <x-status-badge :type="$appeal->status->badgeType()" :label="$appeal->status->label()" />
            <x-status-badge :type="$appeal->application->status->badgeType()" :label="$appeal->application->status->label()" />
        </div>
    </div>
</div>

<section class="mb-6 rounded border border-slate-200 bg-white p-4">
    <h2 class="text-sm font-semibold text-slate-900">{{ __('workflow.appeal.show.reason_heading') }}</h2>
    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $appeal->reason }}</p>
    <p class="mt-2 text-xs text-slate-500">
        {{ __('workflow.common.submitted') }} {{ format_date($appeal->submitted_at, 'j M Y, H:i') }}
    </p>

    @if ($appeal->review_remarks)
        <div class="mt-4 rounded border border-slate-200 bg-slate-50 px-3 py-2">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('workflow.appeal.show.decision_remarks') }}</p>
            <p class="mt-1 text-sm text-slate-700">{{ $appeal->review_remarks }}</p>
            <p class="mt-1 text-xs text-slate-500">
                {{ $appeal->reviewer?->name ?: __('workflow.appeal.show.reviewer') }}
                @if ($appeal->decided_at) · {{ format_date($appeal->decided_at, 'j M Y, H:i') }} @endif
            </p>
        </div>
    @endif
</section>

@if ($canReview)
    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        @if ($appeal->status === \App\Enums\AppealStatus::SUBMITTED)
            <div class="rounded border border-blue-200 bg-blue-50 p-4">
                <h2 class="text-sm font-semibold text-blue-900">{{ __('workflow.appeal.show.step_one_title') }}</h2>
                <p class="mt-1 text-xs text-blue-800">
                    {{ __('workflow.appeal.show.step_one_help') }}
                </p>
                <form method="POST" action="{{ route('appeals.reopen', $appeal) }}" class="mt-3">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">
                        {{ __('workflow.appeal.show.reopen') }}
                    </button>
                </form>
            </div>
        @endif

        @if ($appeal->status === \App\Enums\AppealStatus::UNDER_REVIEW)
            <form method="POST" action="{{ route('appeals.decision', $appeal) }}" class="rounded border border-slate-200 bg-white p-4">
                <h2 class="text-sm font-semibold text-slate-900">{{ __('workflow.appeal.show.step_two_title') }}</h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('workflow.appeal.show.step_two_help') }}
                </p>

                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <label class="flex items-center gap-2 rounded border border-slate-200 px-3 py-2 text-sm">
                        <input type="radio" name="outcome" value="APPROVED" required class="border-slate-300" @checked(old('outcome') === 'APPROVED')>
                        <span>{{ __('workflow.appeal.show.approve_appeal') }}</span>
                    </label>
                    <label class="flex items-center gap-2 rounded border border-slate-200 px-3 py-2 text-sm">
                        <input type="radio" name="outcome" value="REJECTED" required class="border-slate-300" @checked(old('outcome') === 'REJECTED')>
                        <span>{{ __('workflow.appeal.show.reject_appeal') }}</span>
                    </label>
                </div>
                @error('outcome')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <label for="remarks" class="mb-1 mt-3 block text-xs font-medium text-slate-600">{{ __('workflow.appeal.show.remarks_required') }}</label>
                <textarea id="remarks" name="remarks" rows="3" required
                          placeholder="{{ __('workflow.appeal.show.remarks_placeholder') }}"
                          class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">{{ old('remarks') }}</textarea>
                @error('remarks')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <button type="submit" class="mt-3 rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900"
                        onclick="return confirm('{{ __('workflow.appeal.show.confirm_decision') }}');">
                    {{ __('workflow.appeal.show.record_decision') }}
                </button>
            </form>
        @endif
    </div>
@endif

<div class="grid gap-6 lg:grid-cols-3">
    <section class="rounded border border-slate-200 bg-white p-4 lg:col-span-2">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('workflow.appeal.show.original_decision') }}</h2>
        @if ($appeal->application->decision)
            <div class="mt-2 rounded border border-slate-200 px-3 py-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-sm font-medium text-slate-900">{{ $appeal->application->decision->decision->label() }}</span>
                    <span class="text-xs text-slate-500">
                        {{ $appeal->application->decision->decidedBy?->name ?: __('workflow.common.committee') }}
                        @if ($appeal->application->decision->decided_at) · {{ format_date($appeal->application->decision->decided_at, 'j M Y') }} @endif
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-700">{{ $appeal->application->decision->reason }}</p>
            </div>
            @if ($appeal->application->scores->isNotEmpty())
                <p class="mt-2 text-xs text-slate-500">
                    {{ __('workflow.appeal.show.weighted_at_decision') }} <strong class="text-slate-700">{{ number_format($appeal->application->weightedTotal(), 2) }}%</strong>
                </p>
            @endif
        @else
            <p class="mt-2 text-sm text-slate-500">{{ __('workflow.appeal.show.no_original_decision') }}</p>
        @endif

        <h2 class="mt-6 text-sm font-semibold text-slate-900">{{ __('workflow.common.applicant_statement') }}</h2>
        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $appeal->application->statement }}</p>

        <div class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-4">
            <div>
                <p class="text-xs text-slate-500">{{ __('workflow.common.current_grade') }}</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $appeal->application->current_grade ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">{{ __('workflow.common.gpa') }}</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $appeal->application->grade_point_average ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">{{ __('workflow.common.category') }}</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $appeal->application->student->student_category?->label() ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">{{ __('workflow.common.school') }}</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $appeal->application->student->school?->name ?? '—' }}</p>
            </div>
        </div>
    </section>

    <aside class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('workflow.appeal.show.workflow_position') }}</h2>
        <ol class="mt-3 space-y-2 text-sm text-slate-700">
            <li class="rounded border border-slate-200 px-3 py-2">
                {{ __('workflow.appeal.show.decision_recorded', ['decision' => $appeal->application->decision?->decision->label() ?: '—']) }}
            </li>
            <li class="rounded border border-slate-200 px-3 py-2">
                {{ __('workflow.appeal.show.appeal_status', ['status' => $appeal->status->label()]) }}
            </li>
            <li class="rounded border border-slate-200 px-3 py-2">
                {{ __('workflow.appeal.show.application_now', ['status' => $appeal->application->status->label()]) }}
            </li>
        </ol>
        <p class="mt-3 text-xs leading-relaxed text-slate-500">
            {{ __('workflow.appeal.show.visibility_note') }}
        </p>
    </aside>
</div>
@endsection
