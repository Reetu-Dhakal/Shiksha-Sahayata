@extends('layouts.app')

@section('title', __('nav.awards'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">{{ __('nav.awards') }}</h1>
    <p class="mt-1 text-sm text-slate-600">
        Scholarship awards issued to you. Each award has a verification code that anyone can check on the Verify Award page.
    </p>
</div>

@if ($awards->isEmpty())
    <x-empty-state title="No awards yet" message="An award appears here once the selection committee selects your application and the administering office issues it." />
@else
    <div class="space-y-4">
        @foreach ($awards as $award)
            @php($application = $award->application)

            <article class="rounded border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-slate-500">{{ $application->scholarship->title }}</p>
                        <h2 class="mt-1 text-sm font-semibold text-slate-900">
                            Award {{ $award->award_number }}
                        </h2>
                        <p class="mt-1 text-xs text-slate-600">
                            Issued {{ $award->issued_at?->format('j M Y') ?: '—' }}
                            · Verification code <span class="font-mono">{{ $award->verification_code }}</span>
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            Student: {{ $application->student->name }} ({{ $application->student->scholar_student_id }})
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
                        View public verification
                    </a>
                    <a href="{{ route('awards.letter', $award) }}" class="rounded border border-blue-800 px-3 py-1.5 text-xs font-medium text-blue-800 hover:bg-blue-50">
                        Download award letter (PDF)
                    </a>
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection
