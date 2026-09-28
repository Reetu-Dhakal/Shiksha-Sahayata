@extends('layouts.app')

@section('title', __('nav.dashboard'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">Local Education Dashboard</h1>
    <p class="mt-1 text-sm text-slate-600">Applications assigned to your local education unit.</p>
</div>

<x-stat-cards :stats="$dashboard['stats']" />

<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <section class="rounded border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">Next applications in your queue</h2>
            <a href="{{ route('verifications.index') }}" class="text-xs font-medium text-blue-800 hover:underline">{{ __('nav.verifications') }}</a>
        </div>

        @if ($dashboard['rows']->isEmpty())
            <x-empty-state title="Nothing pending" message="Applications awaiting local verification will appear here." />
        @else
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach ($dashboard['rows'] as $application)
                    <li class="flex items-center justify-between gap-3 py-2">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-800">{{ $application->student->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $application->scholarship->title }}</p>
                        </div>
                        <a href="{{ route('verifications.show', $application) }}" class="shrink-0 text-xs font-medium text-blue-800 hover:underline">
                            Open
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">Assisted applications</h2>
            <a href="{{ route('applications.index') }}" class="text-xs font-medium text-blue-800 hover:underline">{{ __('nav.assisted_applications') }}</a>
        </div>
        <p class="mt-2 text-sm text-slate-600">
            Students inside your municipality can be assisted with applications when they cannot apply themselves.
        </p>
        <a href="{{ route('applications.create') }}" class="mt-3 inline-block rounded bg-blue-800 px-4 py-2 text-xs font-medium text-white hover:bg-blue-900">
            New assisted application
        </a>
    </section>
</div>
@endsection
