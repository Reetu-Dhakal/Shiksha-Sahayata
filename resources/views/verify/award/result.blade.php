@extends('layouts.public')

@section('title', __('nav.verify_award'))

@section('content')
<div class="mx-auto max-w-2xl px-4 py-10">
    <h1 class="text-xl font-semibold text-slate-900">{{ __('nav.verify_award') }}</h1>

    <p class="mt-2 text-sm text-slate-600">
        Checked code: <span class="font-mono">{{ $code }}</span>
    </p>

    @if ($award === null)
        <div class="mt-6 rounded border border-red-200 bg-red-50 p-5">
            <p class="text-sm font-semibold text-red-700">No award found</p>
            <p class="mt-1 text-sm text-red-700">
                This verification code does not match any award issued by Shiksha Sahayata. Check the code and try again,
                or contact the scholarship office if the problem continues.
            </p>
        </div>
    @else
        @php($application = $award->application)

        <div class="mt-6 rounded border border-slate-200 bg-white p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-slate-500">Award {{ $award->award_number }}</p>
                    <h2 class="mt-1 text-base font-semibold text-slate-900">{{ $application->scholarship->title }}</h2>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <x-status-badge :type="$award->status->badgeType()" :label="$award->status->label()" />
                    <x-status-badge :type="$award->disbursement_status->badgeType()" :label="$award->disbursement_status->label()" />
                </div>
            </div>

            <table class="mt-4 w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-2 pr-4 text-slate-500">Recipient</td>
                        <td class="py-2 text-slate-800">{{ $application->student->name }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 text-slate-500">Student ID</td>
                        <td class="py-2 text-slate-800">{{ $application->student->scholar_student_id }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 text-slate-500">Issued on</td>
                        <td class="py-2 text-slate-800">{{ $award->issued_at?->format('j F Y') ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 text-slate-500">Verification code</td>
                        <td class="py-2 font-mono text-slate-800">{{ $award->verification_code }}</td>
                    </tr>
                </tbody>
            </table>

            @if ($award->status === \App\Enums\AwardStatus::REVOKED)
                <p class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
                    This award has been revoked by the scholarship office and must not be treated as valid.
                </p>
            @endif
        </div>
    @endif

    <p class="mt-4 text-xs text-slate-500">
        Only publicly shareable details are shown. Documents, contact details and internal review notes are never exposed here.
    </p>
</div>
@endsection
