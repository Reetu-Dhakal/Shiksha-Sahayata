@extends('layouts.public')

@section('title', $scholarship->title)

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8">
    <nav class="mb-4 text-xs text-slate-500">
        <a href="{{ route('scholarships.index') }}" class="hover:text-blue-800">{{ __('nav.browse') }}</a>
        <span class="mx-1">/</span>
        <span>{{ $scholarship->title }}</span>
    </nav>

    <div class="rounded border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs text-slate-500">{{ $scholarship->provider }}</p>
                <h1 class="mt-1 text-xl font-semibold text-slate-900">{{ $scholarship->title }}</h1>
                <div class="mt-3 flex flex-wrap gap-2">
                    <x-status-badge :type="$scholarship->status->badgeType()" :label="$scholarship->status->label()" />
                    @if ($canApply)
                        <x-status-badge type="success" label="{{ __('scholarship.badge.accepting') }}" />
                    @elseif ($scholarship->isExpired())
                        <x-status-badge type="danger" label="{{ __('scholarship.badge.deadline_passed') }}" />
                    @else
                        <x-status-badge type="neutral" label="{{ __('scholarship.badge.not_open') }}" />
                    @endif
                </div>
            </div>

            <div class="text-right">
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('scholarship.show.application_deadline') }}</p>
                <p class="text-lg font-semibold text-slate-900">{{ format_date($scholarship->application_deadline, 'j M Y') }}</p>
                <p class="text-xs text-slate-500">
                    {{ __('scholarship.show.opens', ['date' => format_date($scholarship->application_start, 'j M Y')]) }}
                </p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 border-t border-slate-100 pt-4 sm:grid-cols-4">
            <div>
                <p class="text-xs text-slate-500">{{ __('scholarship.education_level') }}</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $scholarship->education_level?->label() ?? __('scholarship.any_level') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">{{ __('scholarship.grades') }}</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $scholarship->gradeRangeLabel() }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">{{ __('scholarship.show.available_slots') }}</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $scholarship->available_slots ?? __('scholarship.show.not_fixed') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">{{ __('scholarship.show.total_criteria_weight') }}</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ number_format($scholarship->totalWeight(), 2) }}%</p>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            @if ($canApply && Route::has('applications.create'))
                <a href="{{ route('applications.create', $scholarship) }}" class="rounded bg-blue-800 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-900">
                    {{ __('scholarship.show.apply_now') }}
                </a>
            @elseif ($canApply && auth()->check() === false)
                <a href="{{ route('login') }}" class="rounded bg-blue-800 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-900">
                    {{ __('scholarship.show.login_to_apply') }}
                </a>
                <a href="{{ route('register') }}" class="rounded border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    {{ __('nav.register') }}
                </a>
            @elseif ($canApply)
                <a href="{{ route('dashboard') }}" class="rounded bg-blue-800 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-900">
                    {{ __('scholarship.show.dashboard_to_apply') }}
                </a>
            @else
                <p class="rounded border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-600">
                    {{ __('scholarship.show.not_accepting') }}
                </p>
            @endif
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <section class="rounded border border-slate-200 bg-white p-5 lg:col-span-2">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('scholarship.show.about') }}</h2>
            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $scholarship->description }}</p>

            <h2 class="mt-6 text-sm font-semibold text-slate-900">{{ __('scholarship.show.eligibility_rules') }}</h2>
            @if ($scholarship->eligibilityRules->isEmpty())
                <p class="mt-2 text-sm text-slate-500">{{ __('scholarship.show.no_rules') }}</p>
            @else
                <ul class="mt-2 space-y-2">
                    @foreach ($scholarship->eligibilityRules as $rule)
                        <li class="rounded border border-slate-200 px-3 py-2 text-sm text-slate-700">
                            {{ $rule->label() }}
                        </li>
                    @endforeach
                </ul>
                <p class="mt-2 text-xs text-slate-500">{{ __('scholarship.show.rules_note') }}</p>
            @endif

            <h2 class="mt-6 text-sm font-semibold text-slate-900">{{ __('scholarship.show.criteria') }}</h2>
            @if ($scholarship->criteria->isEmpty())
                <p class="mt-2 text-sm text-slate-500">{{ __('scholarship.show.no_criteria') }}</p>
            @else
                <table class="mt-2 w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                            <th class="py-2 font-medium">{{ __('scholarship.show.criterion') }}</th>
                            <th class="py-2 font-medium">{{ __('scholarship.show.description') }}</th>
                            <th class="py-2 text-right font-medium">{{ __('scholarship.show.weight') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($scholarship->criteria as $criterion)
                            <tr class="border-b border-slate-100">
                                <td class="py-2 text-slate-800">{{ $criterion->name }}</td>
                                <td class="py-2 text-slate-600">{{ $criterion->description }}</td>
                                <td class="py-2 text-right font-medium text-slate-900">{{ rtrim(rtrim(number_format((float) $criterion->weight, 2), '0'), '.') }}%</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="py-2 font-semibold text-slate-900" colspan="2">{{ __('scholarship.show.total') }}</td>
                            <td class="py-2 text-right font-semibold text-slate-900">{{ rtrim(rtrim(number_format($scholarship->totalWeight(), 2), '0'), '.') }}%</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </section>

        <aside class="space-y-6">
            <section class="rounded border border-slate-200 bg-white p-5">
                <h2 class="text-sm font-semibold text-slate-900">{{ __('scholarship.show.documents') }}</h2>
                @if ($scholarship->requiredDocuments->isEmpty())
                    <p class="mt-2 text-sm text-slate-500">{{ __('scholarship.show.no_documents') }}</p>
                @else
                    <ul class="mt-2 space-y-2">
                        @foreach ($scholarship->requiredDocuments as $document)
                            <li class="flex items-start justify-between gap-2 text-sm text-slate-700">
                                <span>{{ $document->document_type->label() }}</span>
                                <x-status-badge :type="$document->is_required ? 'warning' : 'neutral'" :label="$document->is_required ? __('scholarship.show.required') : __('scholarship.show.optional')" />
                            </li>
                            @if ($document->description)
                                <li class="text-xs text-slate-500">{{ $document->description }}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="rounded border border-slate-200 bg-white p-5">
                <h2 class="text-sm font-semibold text-slate-900">{{ __('scholarship.show.timeline') }}</h2>
                <dl class="mt-2 space-y-2 text-sm">
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-600">{{ __('scholarship.show.applications_open') }}</dt>
                        <dd class="font-medium text-slate-900">{{ format_date($scholarship->application_start, 'j M Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-600">{{ __('scholarship.deadline') }}</dt>
                        <dd class="font-medium text-slate-900">{{ format_date($scholarship->application_deadline, 'j M Y') }}</dd>
                    </div>
                </dl>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">
                    {{ __('scholarship.show.timeline_note') }}
                </p>
            </section>
        </aside>
    </div>
</div>
@endsection
