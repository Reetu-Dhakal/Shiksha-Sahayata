@extends('layouts.gov')

@section('title', __('common.app_name'))

@section('content')
@php
    $slides = [
        ['key' => 'slide_1', 'image' => asset('images/banner-1.svg'), 'url' => route('scholarships.index')],
        ['key' => 'slide_2', 'image' => asset('images/banner-2.svg'), 'url' => route('home').'#services'],
        ['key' => 'slide_3', 'image' => asset('images/banner-3.svg'), 'url' => route('verify.award.form')],
    ];

    $services = [
        ['key' => 'find', 'icon' => 'search', 'url' => route('scholarships.index')],
        ['key' => 'apply', 'icon' => 'apply', 'url' => $applyUrl],
        ['key' => 'track', 'icon' => 'track', 'url' => $trackUrl],
        ['key' => 'eligibility', 'icon' => 'checklist', 'url' => '#scholarships'],
        ['key' => 'verify', 'icon' => 'verify', 'url' => route('verify.award.form')],
        ['key' => 'updates', 'icon' => 'bell', 'url' => '#notices'],
    ];

    $infoLinks = [
        ['key' => 'process', 'icon' => 'apply', 'url' => '#services'],
        ['key' => 'eligibility', 'icon' => 'checklist', 'url' => '#scholarships'],
        ['key' => 'documents', 'icon' => 'document', 'url' => route('scholarships.index')],
        ['key' => 'verify', 'icon' => 'verify', 'url' => route('verify.award.form')],
        ['key' => 'contact', 'icon' => 'mail', 'url' => '#contact'],
    ];

    $resources = ['policy', 'form', 'criteria', 'award_letter'];

    $externalLinks = ['ministry', 'university_grants', 'curriculum', 'examination_board'];

    $steps = ['step_1', 'step_2', 'step_3', 'step_4', 'step_5', 'step_6'];

    $hasFilters = $filters['q'] !== '' || $filters['level'] !== '' || $filters['grade'] !== null;
@endphp

{{-- Hero carousel --}}
<section id="top" class="relative bg-gov-dark" x-data="heroCarousel()" @mouseenter="stop()" @mouseleave="play()">
    <div class="relative h-[320px] overflow-hidden sm:h-[400px] lg:h-[460px]">
        @foreach ($slides as $i => $slide)
            <div data-slide="{{ $i }}" x-show="index === {{ $i }}" x-transition.opacity.duration.500ms class="absolute inset-0">
                <img src="{{ $slide['image'] }}" alt="{{ __('home.slides.'.$slide['key'].'.alt') }}" class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-gov-dark/55 via-gov-dark/10 to-transparent"></div>

                <div class="absolute bottom-0 left-0 right-0 border-t-4 border-gov-red bg-white/90 px-5 py-4 backdrop-blur-sm sm:bottom-4 sm:left-auto sm:right-4 sm:max-w-md sm:border-l-4 sm:border-t-0 sm:px-6 sm:py-5 sm:shadow-xl">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gov-blue">{{ __('home.slides.'.$slide['key'].'.kicker') }}</p>
                    <h1 class="mt-1 text-lg font-semibold leading-snug text-gov-dark sm:text-2xl">{{ __('home.slides.'.$slide['key'].'.title') }}</h1>
                    <p class="mt-2 text-xs leading-relaxed text-gov-ink sm:text-sm">{{ __('home.slides.'.$slide['key'].'.text') }}</p>
                    <a href="{{ $slide['url'] }}" class="gov-btn gov-btn-primary mt-4 !py-2 !text-xs sm:!text-sm">
                        {{ __('home.slides.'.$slide['key'].'.label') }}
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>
            </div>
        @endforeach

        <div class="absolute left-3 top-3 z-20 flex items-center gap-1.5">
            <button type="button" @click="prev()" aria-label="{{ __('home.carousel.previous') }}"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-white/85 text-gov-dark shadow-sm transition hover:bg-white">
                <x-icon name="chevron-left" class="h-4 w-4" />
            </button>
            <button type="button" @click="next()" aria-label="{{ __('home.carousel.next') }}"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-white/85 text-gov-dark shadow-sm transition hover:bg-white">
                <x-icon name="chevron-right" class="h-4 w-4" />
            </button>
            @foreach ($slides as $i => $slide)
                <button type="button" @click="go({{ $i }})" aria-label="{{ __('home.carousel.go_to', ['num' => $i + 1]) }}"
                        class="h-2 rounded-full transition-all"
                        :class="index === {{ $i }} ? 'w-6 bg-white' : 'w-2 bg-white/60 hover:bg-white/90'"></button>
            @endforeach
        </div>
    </div>
</section>

{{-- Services and application process --}}
<section id="services" class="border-b border-gov-line bg-white py-10 sm:py-12">
    <div class="mx-auto max-w-7xl px-4">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="gov-section-title">{{ __('home.sections.services') }}</h2>
            </div>
            <a href="{{ route('scholarships.index') }}" class="gov-link inline-flex items-center gap-1 text-xs font-semibold">
                {{ __('home.view_all_scholarships') }} <x-icon name="arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <a href="{{ $service['url'] }}" class="gov-card group flex items-start gap-4 p-4 transition hover:border-gov-blue/60 hover:shadow-md">
                    <span class="gov-icon-circle"><x-icon name="{{ $service['icon'] }}" class="h-5 w-5" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-gov-dark group-hover:text-gov-blue">{{ __('home.services.'.$service['key'].'.title') }}</span>
                        <span class="mt-1.5 block text-xs leading-relaxed text-gov-muted">{{ __('home.services.'.$service['key'].'.text') }}</span>
                        <span class="gov-link mt-2 inline-flex items-center gap-1 text-xs font-semibold">
                            {{ __('home.go') }} <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                        </span>
                    </span>
                </a>
            @endforeach
        </div>

        <div class="gov-card mt-6 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-gov-dark">
                {{ __('home.sections.process') }}
            </h3>
            <ol class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($steps as $step)
                    <li class="flex items-start gap-3 rounded-sm border border-gov-line bg-gov-pale p-3">
                        <span class="flex h-7 w-7 flex-none items-center justify-center rounded-full bg-gov-blue text-xs font-semibold text-white">{{ __('home.steps.'.$step.'.num') }}</span>
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-gov-dark">{{ __('home.steps.'.$step.'.title') }}</span>
                        </span>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>

{{-- Important information and notices --}}
<section id="notices" class="bg-gov-light py-10 sm:py-12">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 lg:grid-cols-2">
        <div id="information">
            <h2 class="gov-section-title">{{ __('home.sections.information') }}</h2>

            <ul class="mt-6 space-y-2.5">
                @foreach ($infoLinks as $item)
                    <li>
                        <a href="{{ $item['url'] }}" class="gov-card flex items-start gap-3 p-3.5 transition hover:border-gov-blue/60 hover:shadow-sm">
                            <span class="gov-icon-circle !h-9 !w-9"><x-icon name="{{ $item['icon'] }}" class="h-4 w-4" /></span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold text-gov-dark">{{ __('home.info_links.'.$item['key']) }}</span>
                                    <x-icon name="arrow-right" class="h-4 w-4 flex-none text-gov-blue" />
                                </span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <h2 class="gov-section-title">{{ __('home.sections.notices') }}</h2>

            <ul class="mt-6 space-y-2.5">
                @forelse ($notices as $notice)
                    <li class="gov-card flex items-start gap-3 p-3.5">
                        <span class="w-20 flex-none text-xs font-medium text-gov-dark">{{ format_date($notice['date'], 'j M Y') }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[10px] font-semibold uppercase tracking-wide text-gov-blue">{{ $notice['type'] }}</span>
                            <a href="{{ $notice['url'] }}" class="mt-0.5 block text-sm font-medium leading-snug text-gov-ink hover:text-gov-blue hover:underline">
                                {{ $notice['label'] }}
                            </a>
                        </span>
                    </li>
                @empty
                    <li class="gov-card p-4 text-sm text-gov-muted">{{ __('home.empty.notices') }}</li>
                @endforelse
            </ul>

            <p class="gov-meta mt-3 text-[11px]">
                {{ __('home.notices_note') }}
            </p>
        </div>
    </div>
</section>

{{-- Eligibility and scholarships --}}
<section id="scholarships" class="border-b border-gov-line bg-white py-10 sm:py-12">
    <div class="mx-auto max-w-7xl px-4">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="gov-section-title">{{ __('home.sections.scholarships') }}</h2>
            </div>
            <a href="{{ route('scholarships.index') }}" class="gov-link inline-flex items-center gap-1 text-xs font-semibold">
                {{ __('home.view_full_list') }} <x-icon name="arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>

        <form method="GET" action="{{ route('home') }}" class="gov-card mt-6 p-4 sm:p-5">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="home-q" class="mb-1 block text-xs font-medium text-gov-muted">{{ __('home.filter.search') }}</label>
                    <input id="home-q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="{{ __('home.filter.placeholder') }}"
                           class="gov-input" autocomplete="off">
                </div>
                <div>
                    <label for="home-level" class="mb-1 block text-xs font-medium text-gov-muted">{{ __('home.filter.level') }}</label>
                    <select id="home-level" name="level" class="gov-input">
                        <option value="">{{ __('home.filter.all_levels') }}</option>
                        @foreach ($levels as $level)
                            <option value="{{ $level->value }}" @selected($filters['level'] === $level->value)>{{ $level->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="home-grade" class="mb-1 block text-xs font-medium text-gov-muted">{{ __('home.filter.grade') }}</label>
                    <select id="home-grade" name="grade" class="gov-input">
                        <option value="">{{ __('home.filter.any_grade') }}</option>
                        @for ($grade = 1; $grade <= 12; $grade++)
                            <option value="{{ $grade }}" @selected($filters['grade'] === $grade)>{{ $grade }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="gov-btn gov-btn-primary flex-1 lg:flex-none">
                        <x-icon name="search" class="h-4 w-4" /> {{ __('home.filter.submit') }}
                    </button>
                    <a href="{{ route('home') }}" class="gov-btn gov-btn-outline">{{ __('home.filter.clear') }}</a>
                </div>
            </div>
        </form>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <p class="gov-meta">
                @if ($hasFilters)
                    {{ __('home.results_found', ['count' => $scholarships->count()]) }}
                @else
                    {{ __('home.currently_published') }}
                @endif
            </p>
            <p class="gov-meta">{{ __('home.open_priority') }}</p>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($scholarships as $scholarship)
                <article class="gov-card flex flex-col p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="gov-meta truncate">{{ $scholarship->provider }}</p>
                            <h3 class="mt-1 text-base font-semibold leading-snug text-gov-dark">
                                <a href="{{ route('scholarships.show', $scholarship) }}" class="hover:text-gov-blue hover:underline">
                                    {{ $scholarship->title }}
                                </a>
                            </h3>
                        </div>
                        <x-icon name="graduation" class="h-6 w-6 flex-none text-gov-blue/60" />
                    </div>

                    <p class="mt-2 line-clamp-3 text-xs leading-relaxed text-gov-muted">{{ $scholarship->description }}</p>

                    <dl class="mt-3 space-y-1 text-xs text-gov-muted">
                        <div class="flex justify-between gap-2">
                            <dt>{{ __('home.card.level') }}</dt>
                            <dd class="text-right font-medium text-gov-ink">{{ $scholarship->education_level?->label() ?? __('home.card.any_level') }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt>{{ __('home.card.grades') }}</dt>
                            <dd class="text-right font-medium text-gov-ink">{{ $scholarship->gradeRangeLabel() }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt>{{ __('home.card.deadline') }}</dt>
                            <dd class="text-right font-medium text-gov-ink">{{ format_date($scholarship->application_deadline, 'j M Y') }}</dd>
                        </div>
                    </dl>

                    <div class="gov-divider mt-4 flex items-center justify-between gap-2 pt-3">
                        @if ($scholarship->isAcceptingApplications())
                            <x-status-badge type="success" :label="__('home.badges.open')" />
                        @elseif ($scholarship->isExpired())
                            <x-status-badge type="danger" :label="__('home.badges.expired')" />
                        @else
                            <x-status-badge type="neutral" :label="__('home.badges.not_open')" />
                        @endif
                        <a href="{{ route('scholarships.show', $scholarship) }}" class="gov-link inline-flex items-center gap-1 text-xs font-semibold">
                            {{ __('home.view_details') }} <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                        </a>
                    </div>
                </article>
            @empty
                <div class="gov-card col-span-full px-6 py-10 text-center">
                    <span class="gov-icon-circle mx-auto"><x-icon name="search" class="h-5 w-5" /></span>
                    <h3 class="mt-3 text-sm font-semibold text-gov-dark">{{ __('home.empty.scholarships_title') }}</h3>
                    <p class="mx-auto mt-1 max-w-md text-xs leading-relaxed text-gov-muted">
                        {{ __('home.empty.scholarships_text') }}
                    </p>
                    <div class="mt-4 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('home') }}" class="gov-btn gov-btn-outline">{{ __('home.empty.clear_filters') }}</a>
                        <a href="{{ route('scholarships.index') }}" class="gov-btn gov-btn-primary">{{ __('home.view_all_scholarships') }}</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- Updates --}}
<section id="updates" class="border-b border-gov-line bg-gov-pale py-10 sm:py-12">
    <div class="mx-auto max-w-7xl px-4">
        <h2 class="gov-section-title">{{ __('home.sections.updates') }}</h2>

        <div class="mt-8 grid gap-4 lg:grid-cols-3">
            <div class="gov-card p-5 lg:col-span-2">
                <h3 class="text-sm font-semibold text-gov-dark">
                    {{ __('home.sections.deadlines') }}
                </h3>

                <ul class="mt-4 divide-y divide-gov-line">
                    @forelse ($deadlines as $scholarship)
                        <li class="flex items-center gap-3 py-3">
                            <span class="gov-icon-circle !h-9 !w-9"><x-icon name="calendar" class="h-4 w-4" /></span>
                            <span class="min-w-0 flex-1">
                                <a href="{{ route('scholarships.show', $scholarship) }}" class="block truncate text-sm font-medium text-gov-ink hover:text-gov-blue hover:underline">
                                    {{ $scholarship->title }}
                                </a>
                                <span class="gov-meta block truncate">{{ $scholarship->provider }} · {{ $scholarship->education_level?->label() ?? __('home.card.any_level') }}</span>
                            </span>
                            <span class="flex flex-none flex-col items-end gap-1">
                                <span class="text-xs font-semibold text-gov-dark">{{ format_date($scholarship->application_deadline, 'j M Y') }}</span>
                                @if ($scholarship->isAcceptingApplications())
                                    <x-status-badge type="success" :label="__('home.badges.short_open')" class="!text-[10px]" />
                                @else
                                    <x-status-badge type="neutral" :label="__('home.badges.not_open')" class="!text-[10px]" />
                                @endif
                            </span>
                        </li>
                    @empty
                        <li class="py-4 text-sm text-gov-muted">{{ __('home.empty.deadlines') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="gov-card p-5">
                <h3 class="text-sm font-semibold text-gov-dark">
                    {{ __('home.sections.publications') }}
                </h3>

                <ul class="mt-4 space-y-3">
                    @forelse ($publications as $scholarship)
                        <li class="border-b border-gov-line pb-3 last:border-0 last:pb-0">
                            <span class="gov-meta block">{{ format_date($scholarship->created_at, 'j M Y') }}</span>
                            <a href="{{ route('scholarships.show', $scholarship) }}" class="mt-0.5 block text-sm font-medium leading-snug text-gov-ink hover:text-gov-blue hover:underline">
                                {{ $scholarship->title }}
                            </a>
                        </li>
                    @empty
                        <li class="text-sm text-gov-muted">{{ __('home.empty.publications') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Resources and external links --}}
<section id="resources" class="bg-white py-10 sm:py-12">
    <div class="mx-auto max-w-7xl px-4">
        <h2 class="gov-section-title">{{ __('home.sections.resources') }}</h2>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($resources as $resource)
                <div class="gov-card flex flex-col p-4">
                    <div class="flex items-start justify-between gap-2">
                        <span class="gov-icon-circle !h-9 !w-9"><x-icon name="document" class="h-4 w-4" /></span>
                        <span class="rounded-sm border border-gov-line bg-gov-pale px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-gov-muted">{{ __('home.demo_badge') }}</span>
                    </div>
                    <h3 class="mt-3 text-sm font-semibold leading-snug text-gov-dark">{{ __('home.resources.'.$resource) }}</h3>
                    <p class="gov-meta mt-3 border-t border-gov-line pt-2 text-[11px]">{{ __('home.resource_unavailable') }}</p>
                </div>
            @endforeach
        </div>

        <div id="links" class="mt-10">
            <h3 class="text-sm font-semibold text-gov-dark">
                {{ __('home.sections.external_links') }}
            </h3>

            <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($externalLinks as $external)
                    <li class="gov-card flex items-center gap-3 p-3.5 text-gov-muted">
                        <x-icon name="external" class="h-5 w-5 flex-none text-gov-blue/60" />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-gov-ink">{{ __('home.external_links.'.$external) }}</span>
                        </span>
                        <span class="rounded-sm border border-gov-line bg-gov-pale px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-gov-muted">{{ __('home.demo_badge') }}</span>
                    </li>
                @endforeach
            </ul>

            <p class="gov-meta mt-3 text-[11px]">
                {{ __('home.external_note') }}
            </p>
        </div>
    </div>
</section>
@endsection
