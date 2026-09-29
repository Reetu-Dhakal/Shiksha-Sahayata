@extends('layouts.app')

@section('title', __('nav.appeals'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">{{ __('nav.appeals') }}</h1>
    <p class="mt-1 text-sm text-slate-600">
        {{ __('workflow.appeal.index.intro') }}
    </p>
</div>

@if ($appeals->isEmpty())
    <x-empty-state :title="__('workflow.appeal.index.empty_title')" :message="__('workflow.appeal.index.empty_message')" />
@else
    <div class="space-y-3">
        @foreach ($appeals as $appeal)
            <article class="rounded border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-slate-500">{{ $appeal->application->scholarship->title }}</p>
                        <h2 class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $appeal->application->student->name }} · {{ $appeal->application->student->scholar_student_id }}
                        </h2>
                        <p class="mt-1 line-clamp-2 text-xs text-slate-600">{{ $appeal->reason }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ __('workflow.common.submitted') }} {{ format_date($appeal->submitted_at, 'j M Y, H:i') }}</p>
                    </div>
                    <x-status-badge :type="$appeal->status->badgeType()" :label="$appeal->status->label()" />
                </div>

                <div class="mt-3 flex justify-end border-t border-slate-100 pt-3">
                    <a href="{{ route('appeals.show', $appeal) }}" class="text-xs font-medium text-blue-800 hover:underline">
                        {{ __('workflow.appeal.index.open') }}
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $appeals->links() }}
    </div>
@endif
@endsection
