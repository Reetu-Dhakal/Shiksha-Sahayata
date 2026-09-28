@extends('layouts.app')

@section('title', __('nav.dashboard'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">Administration Dashboard</h1>
    <p class="mt-1 text-sm text-slate-600">System-wide status based on live database records.</p>
</div>

<x-stat-cards :stats="$dashboard['stats']" />

<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <section class="rounded border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">Applications by status</h2>
            @if (Route::has('admin.reports.index'))
                <a href="{{ route('admin.reports.index') }}" class="text-xs font-medium text-blue-800 hover:underline">{{ __('nav.reports') }}</a>
            @endif
        </div>

        @if ($dashboard['byStatus']->isEmpty())
            <x-empty-state title="No applications yet" message="Applications will be counted here as soon as students apply." />
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

    <section class="rounded border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">Recent activity</h2>
            @if (Route::has('admin.audit-logs.index'))
                <a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-medium text-blue-800 hover:underline">{{ __('nav.audit_logs') }}</a>
            @endif
        </div>

        @if ($dashboard['recent']->isEmpty())
            <x-empty-state title="No activity yet" message="State changes recorded by the workflow will appear here." />
        @else
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach ($dashboard['recent'] as $log)
                    <li class="py-2">
                        <p class="text-slate-700">{{ $log->description }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">
                            {{ $log->actor?->name ?? 'System' }} · {{ $log->created_at->format('j M Y, H:i') }}
                        </p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
@endsection
