@extends('layouts.app')

@section('title', __('nav.dashboard'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">{{ auth()->user()->role === \App\Enums\Role::GUARDIAN ? 'Guardian Dashboard' : 'Student Dashboard' }}</h1>
    <p class="mt-1 text-sm text-slate-600">Your profile, potentially suitable scholarships, applications and awards.</p>
</div>

<x-stat-cards :stats="$dashboard['stats']" />

<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">{{ auth()->user()->role === \App\Enums\Role::GUARDIAN ? 'Guardian profile' : 'Student profile' }}</h2>
        @if (auth()->user()->role === \App\Enums\Role::GUARDIAN)
            @if (auth()->user()->guardian)
                <p class="mt-2 text-sm text-slate-600">
                    {{ auth()->user()->guardian->name }} ({{ auth()->user()->guardian->relationship }})
                </p>
                <a href="{{ route('guardian.profile.show') }}" class="mt-3 inline-block text-sm font-medium text-blue-800 hover:underline">{{ __('nav.profile') }}</a>
            @else
                <x-empty-state title="No guardian profile yet" message="Create your guardian profile, then link your child's Scholar Student ID.">
                    <a href="{{ route('guardian.profile.show') }}" class="rounded bg-blue-800 px-4 py-2 text-xs font-medium text-white hover:bg-blue-900">Create profile</a>
                </x-empty-state>
            @endif
        @elseif (auth()->user()->student)
            <p class="mt-2 text-sm text-slate-600">
                Scholar Student ID: <span class="font-medium text-slate-800">{{ auth()->user()->student->scholar_student_id }}</span>
            </p>
            <p class="mt-1 text-sm text-slate-600">
                Profile status:
                @if (auth()->user()->student->isVerified())
                    <x-status-badge type="success" label="Verified" />
                @else
                    <x-status-badge type="warning" label="Not yet verified" />
                @endif
            </p>
            <a href="{{ route('profile.show') }}" class="mt-3 inline-block text-sm font-medium text-blue-800 hover:underline">{{ __('nav.profile') }}</a>
        @else
            <x-empty-state title="No student profile yet" message="Complete your student profile to receive your Scholar Student ID and start applying.">
                <a href="{{ route('profile.create') }}" class="rounded bg-blue-800 px-4 py-2 text-xs font-medium text-white hover:bg-blue-900">Complete profile</a>
            </x-empty-state>
        @endif
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">My applications</h2>
            <a href="{{ route('applications.index') }}" class="text-xs font-medium text-blue-800 hover:underline">{{ __('nav.applications') }}</a>
        </div>
        <div class="mt-2">
            @if ($dashboard['rows']->isEmpty())
                <x-empty-state title="No applications yet" message="Once you apply for a scholarship, its progress will appear here.">
                    @if (Route::has('scholarships.index'))
                        <a href="{{ route('scholarships.index') }}" class="rounded border border-slate-300 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">{{ __('nav.browse') }}</a>
                    @endif
                </x-empty-state>
            @else
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach ($dashboard['rows'] as $application)
                        <li class="flex items-center justify-between gap-3 py-2">
                            <div class="min-w-0">
                                <a href="{{ route('applications.show', $application) }}" class="block truncate font-medium text-slate-800 hover:text-blue-800">
                                    {{ $application->scholarship->title }}
                                </a>
                                <p class="text-xs text-slate-500">
                                    {{ $application->submitted_at?->format('j M Y') ?? 'Draft' }}
                                </p>
                            </div>
                            <x-status-badge :type="$application->status->badgeType()" :label="$application->status->label()" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    @if (Route::has('notifications.index'))
    <section class="rounded border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('nav.notifications') }}</h2>
            <a href="{{ route('notifications.index') }}" class="text-xs font-medium text-blue-800 hover:underline">View all</a>
        </div>
        <div class="mt-2">
            @forelse ($notifications as $notification)
                <div class="border-b border-slate-100 py-2 last:border-0">
                    <p class="text-sm font-medium text-slate-800">{{ $notification->title() }}</p>
                    <p class="mt-0.5 text-sm text-slate-600">{{ $notification->body() }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
            @empty
                <p class="py-3 text-sm text-slate-500">{{ __('common.no_records') }}</p>
            @endforelse
        </div>
    </section>
    @endif
</div>
@endsection
