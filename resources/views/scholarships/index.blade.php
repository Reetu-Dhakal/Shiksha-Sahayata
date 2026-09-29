@extends('layouts.public')

@section('title', __('nav.browse'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">{{ __('nav.browse') }}</h1>
    <p class="mt-1 text-sm text-slate-600">{{ __('scholarship.index.intro') }}</p>
</div>

<form method="GET" action="{{ route('scholarships.index') }}" class="mb-6 rounded border border-slate-200 bg-white p-4">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="q" class="mb-1 block text-xs font-medium text-slate-600">{{ __('scholarship.index.search_label') }}</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="{{ __('scholarship.index.search_placeholder') }}"
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
        </div>
        <div>
            <label for="level" class="mb-1 block text-xs font-medium text-slate-600">{{ __('scholarship.index.level_label') }}</label>
            <select id="level" name="level"
                    class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                <option value="">{{ __('scholarship.any_level') }}</option>
                @foreach ($levels as $level)
                    <option value="{{ $level->value }}" @selected($filters['level'] === $level->value)>{{ $level->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="sort" class="mb-1 block text-xs font-medium text-slate-600">{{ __('scholarship.index.sort_label') }}</label>
            <select id="sort" name="sort"
                    class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                <option value="deadline" @selected($filters['sort'] === 'deadline')>{{ __('scholarship.index.sort_deadline') }}</option>
                <option value="newest" @selected($filters['sort'] === 'newest')>{{ __('scholarship.index.sort_newest') }}</option>
            </select>
        </div>
        <div class="flex items-end gap-4">
            <label class="flex items-center gap-2 pb-2 text-sm text-slate-700">
                <input type="checkbox" name="open" value="1" @checked($filters['open']) class="rounded border-slate-300">
                {{ __('scholarship.index.open_only') }}
            </label>
            <button type="submit" class="rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">{{ __('scholarship.index.apply_filters') }}</button>
        </div>
    </div>
</form>

@php $count = $scholarships->total(); @endphp
<p class="mb-3 text-xs text-slate-500">{{ trans_choice('scholarship.index.results', $count) }}</p>

@if ($count === 0)
    <x-empty-state :title="__('scholarship.index.empty_title')" :message="__('scholarship.index.empty_message')">
        <a href="{{ route('scholarships.index') }}" class="text-sm font-medium text-blue-800 hover:underline">{{ __('scholarship.index.clear_filters') }}</a>
    </x-empty-state>
@else
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($scholarships as $scholarship)
            <article class="flex flex-col rounded border border-slate-200 bg-white p-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-xs text-slate-500">{{ $scholarship->provider }}</p>
                        <h2 class="mt-1 text-sm font-semibold text-slate-900">
                            <a href="{{ route('scholarships.show', $scholarship) }}" class="hover:text-blue-800">{{ $scholarship->title }}</a>
                        </h2>
                    </div>
                    <x-status-badge :type="$scholarship->status->badgeType()" :label="$scholarship->status->label()" />
                </div>

                <p class="mt-2 line-clamp-3 text-xs leading-relaxed text-slate-600">{{ $scholarship->description }}</p>

                <dl class="mt-3 space-y-1 text-xs text-slate-600">
                    <div class="flex justify-between gap-2">
                        <dt>{{ __('scholarship.index.card_level') }}</dt>
                        <dd class="font-medium text-slate-800">{{ $scholarship->education_level?->label() ?? __('scholarship.any_level') }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt>{{ __('scholarship.grades') }}</dt>
                        <dd class="font-medium text-slate-800">{{ $scholarship->gradeRangeLabel() }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt>{{ __('scholarship.deadline') }}</dt>
                        <dd class="font-medium text-slate-800">{{ format_date($scholarship->application_deadline, 'j M Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt>{{ __('scholarship.index.card_slots') }}</dt>
                        <dd class="font-medium text-slate-800">{{ $scholarship->available_slots ?? '—' }}</dd>
                    </div>
                </dl>

                <div class="mt-3 flex items-center justify-between gap-2 border-t border-slate-100 pt-3">
                    @if ($scholarship->isAcceptingApplications())
                        <x-status-badge type="success" label="{{ __('scholarship.badge.accepting') }}" />
                    @elseif ($scholarship->isExpired())
                        <x-status-badge type="danger" label="{{ __('scholarship.badge.deadline_passed') }}" />
                    @else
                        <x-status-badge type="neutral" label="{{ __('scholarship.badge.not_open') }}" />
                    @endif
                    <a href="{{ route('scholarships.show', $scholarship) }}" class="text-xs font-medium text-blue-800 hover:underline">{{ __('scholarship.index.view_details') }}</a>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $scholarships->links() }}
    </div>
@endif
@endsection
