@extends('layouts.app')

@section('title', __('nav.verifications'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">{{ __('nav.verifications') }}</h1>
    <p class="mt-1 text-sm text-slate-600">
        {{ __('workflow.verification.index.intro', ['stage' => $stage->label()]) }}
    </p>
</div>

@if ($applications->isEmpty())
    <x-empty-state :title="__('workflow.verification.index.empty_title')" :message="__('workflow.verification.index.empty_message')" />
@else
    <div class="space-y-3">
        @foreach ($applications as $application)
            <article class="rounded border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-slate-500">{{ $application->scholarship->title }}</p>
                        <h2 class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $application->student->name }} · {{ $application->student->scholar_student_id }}
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $application->student->school?->name ?? __('workflow.common.no_school') }}
                            · {{ __('workflow.common.grade') }} {{ $application->student->grade }}
                            @if ($application->is_assisted) · {{ __('workflow.verification.index.assisted') }} @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <x-status-badge :type="$application->status->badgeType()" :label="$application->status->label()" />
                        <p class="mt-2 text-xs text-slate-500">
                            {{ __('workflow.common.submitted') }} {{ format_date($application->submitted_at, 'j M Y, H:i') }}
                        </p>
                    </div>
                </div>

                <div class="mt-3 flex justify-end border-t border-slate-100 pt-3">
                    <a href="{{ route('verifications.show', $application) }}" class="text-xs font-medium text-blue-800 hover:underline">
                        {{ __('workflow.verification.index.open') }}
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $applications->links() }}
    </div>
@endif
@endsection
