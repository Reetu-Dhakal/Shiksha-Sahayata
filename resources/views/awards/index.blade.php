@extends('layouts.app')

@section('title', __('nav.awards'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">{{ __('nav.awards') }}</h1>
    <p class="mt-1 text-sm text-slate-600">
        {{ __('award.index.intro') }}
    </p>
</div>

@if ($awards->isEmpty())
    <x-empty-state :title="__('award.index.empty_title')" :message="__('award.index.empty_message')" />
@else
    <div class="space-y-4">
        @foreach ($awards as $award)
            @php($application = $award->application)

            <article class="rounded border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-slate-500">{{ $application->scholarship->title }}</p>
                        <h2 class="mt-1 text-sm font-semibold text-slate-900">
                            {{ __('award.index.award_prefix') }} {{ $award->award_number }}
                        </h2>
                        <p class="mt-1 text-xs text-slate-600">
                            {{ __('award.index.issued') }} {{ format_date($award->issued_at, 'j M Y') ?: '—' }}
                            · {{ __('award.index.verification_code') }} <span class="font-mono">{{ $award->verification_code }}</span>
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ __('award.index.student') }}: {{ $application->student->name }} ({{ $application->student->scholar_student_id }})
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <x-status-badge :type="$award->status->badgeType()" :label="$award->status->label()" />
                        <x-status-badge :type="$award->disbursement_status->badgeType()" :label="$award->disbursement_status->label()" />
                    </div>
                </div>

                @if ($award->disbursement_remarks)
                    <p class="mt-3 rounded bg-slate-50 p-2 text-xs text-slate-600">{{ $award->disbursement_remarks }}</p>
                @endif

                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
                    <a href="{{ route('verify.award.show', $award->verification_code) }}" class="text-xs font-medium text-blue-800 hover:underline">
                        {{ __('award.index.view_verification') }}
                    </a>
                    <a href="{{ route('awards.letter', $award) }}" class="rounded border border-blue-800 px-3 py-1.5 text-xs font-medium text-blue-800 hover:bg-blue-50">
                        {{ __('award.index.download_letter') }}
                    </a>
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection
