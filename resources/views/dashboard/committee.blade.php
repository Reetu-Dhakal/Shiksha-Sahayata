@extends('layouts.app')

@section('title', __('nav.dashboard'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">Selection Committee Dashboard</h1>
    <p class="mt-1 text-sm text-slate-600">Verified applications ready for review on scholarships you are assigned to.</p>
</div>

<x-stat-cards :stats="$dashboard['stats']" />

<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <section class="rounded border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">Ready for review</h2>
            <a href="{{ route('selection.index') }}" class="text-xs font-medium text-blue-800 hover:underline">{{ __('nav.selection') }}</a>
        </div>

        @if ($dashboard['rows']->isEmpty())
            <x-empty-state title="Nothing to review" message="Applications that pass all required verifications will appear here." />
        @else
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach ($dashboard['rows'] as $application)
                    <li class="flex items-center justify-between gap-3 py-2">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-800">{{ $application->student->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $application->scholarship->title }}</p>
                        </div>
                        <a href="{{ route('selection.show', $application) }}" class="shrink-0 text-xs font-medium text-blue-800 hover:underline">
                            Open
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">Decisions recorded</h2>
            <a href="{{ route('appeals.index') }}" class="text-xs font-medium text-blue-800 hover:underline">{{ __('nav.appeals') }}</a>
        </div>

        @if ($dashboard['byStatus']->isEmpty())
            <x-empty-state title="No decisions yet" message="Selected, waitlisted and rejected counts will appear here." />
        @else
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach ($dashboard['byStatus'] as $label => $count)
                    <li class="flex items-center justify-between py-2">
                        <span class="text-slate-600">{{ $label }}</span>
                        <span class="font-medium text-slate-900">{{ $count }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
@endsection
