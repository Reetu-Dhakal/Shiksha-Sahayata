@extends('layouts.app')

@section('title', 'My applications')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-lg font-semibold text-slate-900">My applications</h1>
        <p class="mt-1 text-sm text-slate-600">Track every application from submission through verification and selection.</p>
    </div>
    <a href="{{ route('applications.create') }}" class="rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">
        Apply for a scholarship
    </a>
</div>

@if ($applications->isEmpty())
    <x-empty-state title="No applications yet" message="Browse open scholarships and submit your first application.">
        <a href="{{ route('scholarships.index') }}" class="text-sm font-medium text-blue-800 hover:underline">Browse scholarships</a>
    </x-empty-state>
@else
    <div class="space-y-3">
        @foreach ($applications as $application)
            <article class="rounded border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-slate-500">{{ $application->scholarship->provider }}</p>
                        <h2 class="mt-1 text-sm font-semibold text-slate-900">
                            <a href="{{ route('applications.show', $application) }}" class="hover:text-blue-800">
                                {{ $application->scholarship->title }}
                            </a>
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Applicant: {{ $application->student->name }} · {{ $application->student->scholar_student_id }}
                            @if ($application->is_assisted) · <span class="font-medium">Assisted application</span> @endif
                        </p>
                    </div>
                    <x-status-badge :type="$application->status->badgeType()" :label="$application->status->label()" />
                </div>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3 text-xs text-slate-500">
                    <span>
                        {{ $application->submitted_at?->format('j M Y, H:i') ? 'Submitted '.$application->submitted_at->format('j M Y, H:i') : 'Not submitted yet · deadline '.$application->scholarship->application_deadline->format('j M Y') }}
                    </span>
                    <div class="flex gap-3">
                        <a href="{{ route('applications.show', $application) }}" class="font-medium text-blue-800 hover:underline">View</a>
                        @if ($application->isEditable())
                            <a href="{{ route('applications.edit', $application) }}" class="font-medium text-blue-800 hover:underline">Edit</a>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $applications->links() }}
    </div>
@endif
@endsection
