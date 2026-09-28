@extends('layouts.public')

@section('title', __('common.app_name'))

@section('content')
<div class="border-b border-slate-200 bg-white">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <p class="text-xs font-semibold uppercase tracking-wide text-blue-800">Government to Citizen (G2C) e-governance prototype</p>
        <h1 class="mt-2 max-w-3xl text-2xl font-semibold text-slate-900 sm:text-3xl">{{ __('common.app_name') }}</h1>
        <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
            {{ __('common.tagline') }}. Discover scholarships, apply online, follow every verification and
            selection step, appeal decisions, and verify awards — in one transparent place.
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
            @if (Route::has('scholarships.index'))
                <a href="{{ route('scholarships.index') }}" class="rounded bg-blue-800 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-900">
                    {{ __('nav.browse') }}
                </a>
            @endif
            @auth
                <a href="{{ route('dashboard') }}" class="rounded border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    {{ __('nav.dashboard') }}
                </a>
            @else
                <a href="{{ route('register') }}" class="rounded border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    {{ __('nav.register') }}
                </a>
            @endauth
        </div>
    </div>
</div>

<div class="mx-auto max-w-6xl px-4 py-8">
    <h2 class="text-base font-semibold text-slate-900">How the system works</h2>
    <p class="mt-1 text-sm text-slate-600">Every scholarship moves through the same transparent workflow.</p>

    <ol class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Discovery', 'Browse published scholarships, deadlines, eligibility rules and required documents.'],
            ['Application', 'Apply online with your student profile and required documents — or with assisted support.'],
            ['Verification', 'School officers verify enrollment, local education officers verify local criteria.'],
            ['Selection', 'The committee reviews verified applications against configured criteria and records a reasoned decision.'],
            ['Appeal', 'Rejected applicants can submit an appeal with supporting documents for review.'],
            ['Award', 'Selected applicants receive a digital award letter with a QR verification code.'],
            ['Tracking', 'Disbursement status is tracked administratively and shown to the applicant.'],
            ['Accountability', 'Important administrative actions are recorded in an audit trail.'],
        ] as $i => [$title, $text])
            <li class="rounded border border-slate-200 bg-white p-4">
                <span class="text-xs font-semibold text-blue-800">Step {{ $i + 1 }}</span>
                <h3 class="mt-1 text-sm font-medium text-slate-900">{{ $title }}</h3>
                <p class="mt-1 text-xs leading-relaxed text-slate-600">{{ $text }}</p>
            </li>
        @endforeach
    </ol>

    <div class="mt-8 grid gap-4 md:grid-cols-3">
        <div class="rounded border border-slate-200 bg-white p-4">
            <h3 class="text-sm font-semibold text-slate-900">For students and guardians</h3>
            <p class="mt-1 text-xs leading-relaxed text-slate-600">
                Create a profile without a National ID, receive a Scholar Student ID, find potentially suitable
                scholarships, apply, track progress and appeal decisions.
            </p>
        </div>
        <div class="rounded border border-slate-200 bg-white p-4">
            <h3 class="text-sm font-semibold text-slate-900">For schools and local education units</h3>
            <p class="mt-1 text-xs leading-relaxed text-slate-600">
                Verify enrollment and local criteria for your jurisdiction, add remarks, return applications for
                correction, and assist students who cannot apply themselves.
            </p>
        </div>
        <div class="rounded border border-slate-200 bg-white p-4">
            <h3 class="text-sm font-semibold text-slate-900">For selection committees</h3>
            <p class="mt-1 text-xs leading-relaxed text-slate-600">
                Review verified applications, see weighted scores calculated from configured criteria, and record
                selected, waitlisted or rejected decisions with reasons.
            </p>
        </div>
    </div>

    @if (Route::has('verify.award.form'))
        <div class="mt-8 rounded border border-blue-200 bg-blue-50 p-4">
            <h3 class="text-sm font-semibold text-blue-900">{{ __('nav.verify_award') }}</h3>
            <p class="mt-1 text-xs leading-relaxed text-blue-800">
                Anyone can verify a scholarship award using the verification code printed on the award letter.
                Verification reveals only limited, non-sensitive information.
            </p>
            <a href="{{ route('verify.award.form') }}" class="mt-3 inline-block rounded bg-blue-800 px-4 py-2 text-xs font-medium text-white hover:bg-blue-900">
                Verify an award
            </a>
        </div>
    @endif
</div>
@endsection
