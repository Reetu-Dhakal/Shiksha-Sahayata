@extends('layouts.app')

@section('title', __('nav.reports'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-lg font-semibold text-slate-900">{{ __('nav.reports') }}</h1>
        <p class="mt-1 text-sm text-slate-600">
            Aggregated figures across the whole system, recalculated from live records each time you open this page.
        </p>
    </div>
    <a href="{{ route('admin.reports.applications-csv') }}"
       class="rounded border border-blue-800 px-4 py-2 text-sm font-medium text-blue-800 hover:bg-blue-50">
        Download applications CSV
    </a>
</div>

<div class="grid gap-4 lg:grid-cols-3">
    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Totals</h2>
        <ul class="mt-3 divide-y divide-slate-100 text-sm">
            @foreach ($totals as $total)
                <li class="flex items-center justify-between py-2">
                    <span class="text-slate-600">{{ $total['label'] }}</span>
                    <span class="font-medium text-slate-900">{{ $total['value'] }}</span>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Applications by status</h2>
        @if ($byStatus->isEmpty())
            <x-empty-state title="No applications" message="Application counts by status will appear here." />
        @else
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach ($byStatus as $label => $count)
                    <li class="flex items-center justify-between py-2">
                        <span class="text-slate-600">{{ $label }}</span>
                        <span class="font-medium text-slate-900">{{ $count }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Selection decisions</h2>
        @if ($decisions->isEmpty())
            <x-empty-state title="No decisions" message="Selected, waitlisted and rejected counts will appear here." />
        @else
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach ($decisions as $label => $count)
                    <li class="flex items-center justify-between py-2">
                        <span class="text-slate-600">{{ $label }}</span>
                        <span class="font-medium text-slate-900">{{ $count }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Applications by scholarship</h2>
        @if ($byScholarship->isEmpty())
            <x-empty-state title="No applications" message="Per-scholarship totals will appear here." />
        @else
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach ($byScholarship as $label => $count)
                    <li class="flex items-start justify-between gap-3 py-2">
                        <span class="text-slate-600">{{ $label }}</span>
                        <span class="font-medium text-slate-900">{{ $count }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Applications by district</h2>
        @if ($byDistrict->isEmpty())
            <x-empty-state title="No applications" message="District totals will appear here." />
        @else
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach ($byDistrict as $label => $count)
                    <li class="flex items-center justify-between py-2">
                        <span class="text-slate-600">{{ $label ?: 'Unknown' }}</span>
                        <span class="font-medium text-slate-900">{{ $count }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Disbursement progress</h2>
        @if ($disbursement->isEmpty())
            <x-empty-state title="No awards" message="Disbursement stage totals will appear once awards are issued." />
        @else
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach ($disbursement as $label => $count)
                    <li class="flex items-center justify-between py-2">
                        <span class="text-slate-600">{{ $label }}</span>
                        <span class="font-medium text-slate-900">{{ $count }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>

<div class="mt-6 rounded border border-slate-200 bg-white p-4">
    <h2 class="text-sm font-semibold text-slate-900">Verification outcomes</h2>
    @if ($verificationOutcomes->isEmpty())
        <x-empty-state title="No verification records" message="Verified and returned counts will appear here." />
    @else
        <ul class="mt-3 divide-y divide-slate-100 text-sm">
            @foreach ($verificationOutcomes as $label => $count)
                <li class="flex items-center justify-between py-2">
                    <span class="text-slate-600">{{ \App\Enums\VerificationStatus::from($label)->label() }}</span>
                    <span class="font-medium text-slate-900">{{ $count }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
