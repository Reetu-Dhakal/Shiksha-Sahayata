@extends('layouts.gov')

@section('title', __('common.app_name'))

@section('content')
@php
    $slides = [
        [
            'image' => asset('images/banner-1.svg'),
            'alt' => 'विद्यालय तथा विद्यार्थी — डेमो ब्यानर चित्र',
            'kicker' => 'छात्रवृत्ति सेवा',
            'title' => 'गुणस्तरीय शिक्षाका लागि छात्रवृत्ति सजिलै',
            'en' => 'Scholarships made simple for quality education',
            'text' => 'प्रकाशित छात्रवृत्ति, पात्रता र अन्तिम मिति एकै ठाउँमा। अनलाइन आवेदन गर्नुहोस् र स्थिति ट्र्याक गर्नुहोस्।',
            'label' => 'छात्रवृत्ति हेर्नुहोस्',
            'url' => route('scholarships.index'),
        ],
        [
            'image' => asset('images/banner-2.svg'),
            'alt' => 'कक्षाकोठा — डेमो ब्यानर चित्र',
            'kicker' => 'पारदर्शी आवेदन',
            'title' => 'अनलाइन आवेदन, चरणबद्ध प्रमाणीकरण र चयन',
            'en' => 'Online application, step-by-step verification and selection',
            'text' => 'दर्तादेखि पुरस्कारसम्मको हरेक चरण अनलाइन ट्र्याक गर्न सकिन्छ। प्रत्येक निर्णय कारणसहित रेकर्ड हुन्छ।',
            'label' => 'आवेदन प्रक्रिया हेर्नुहोस्',
            'url' => route('home').'#services',
        ],
        [
            'image' => asset('images/banner-3.svg'),
            'alt' => 'पुस्तक र प्रमाणपत्र — डेमो ब्यानर चित्र',
            'kicker' => 'पुरस्कार सेवा',
            'title' => 'पुरस्कार पत्र अनलाइन प्रमाणीकरण',
            'en' => 'Verify award letters online',
            'text' => 'पुरस्कारपत्रमा रहेको प्रमाणीकरण कोड प्रयोग गरी कुनै पनि पुरस्कारको प्रामाणिकता जाँच्न सकिन्छ।',
            'label' => 'पुरस्कार प्रमाणीकरण',
            'url' => route('verify.award.form'),
        ],
    ];

    $services = [
        ['icon' => 'search', 'title' => 'छात्रवृत्ति खोज्नुहोस्', 'en' => 'Find scholarships', 'text' => 'तह, कक्षा र शब्दअनुसार प्रकाशित छात्रवृत्ति खोज्नुहोस्।', 'url' => route('scholarships.index')],
        ['icon' => 'apply', 'title' => 'आवेदन गर्नुहोस्', 'en' => 'Apply online', 'text' => 'प्रोफाइल र कागजातसहित आवेदन अनलाइन पेश गर्नुहोस्।', 'url' => $applyUrl],
        ['icon' => 'track', 'title' => 'आवेदन स्थिति हेर्नुहोस्', 'en' => 'Track application', 'text' => 'प्रमाणीकरण, चयन र निर्णयको अवस्था ट्र्याक गर्नुहोस्।', 'url' => $trackUrl],
        ['icon' => 'checklist', 'title' => 'पात्रता जाँच्नुहोस्', 'en' => 'Check eligibility', 'text' => 'तह र कक्षा अनुसार मिल्दो छात्रवृत्ति हेर्नुहोस्।', 'url' => '#scholarships'],
        ['icon' => 'verify', 'title' => 'पुरस्कार प्रमाणीकरण', 'en' => 'Verify award', 'text' => 'प्रमाणीकरण कोडमार्फत पुरस्कारको प्रामाणिकता जाँच्नुहोस्।', 'url' => route('verify.award.form')],
        ['icon' => 'bell', 'title' => 'सूचना तथा अपडेट', 'en' => 'Notices and updates', 'text' => 'नयाँ प्रकाशन र आगामी अन्तिम मिति सूचीबाट जान्नुहोस्।', 'url' => '#notices'],
    ];

    $infoLinks = [
        ['icon' => 'apply', 'title' => 'आवेदन प्रक्रिया र सेवाहरू', 'en' => 'Application process and online services', 'url' => '#services'],
        ['icon' => 'checklist', 'title' => 'पात्रता मापदण्ड र तह', 'en' => 'Eligibility criteria and education levels', 'url' => '#scholarships'],
        ['icon' => 'document', 'title' => 'आवश्यक कागजातहरू', 'en' => 'Documents required for each scholarship', 'url' => route('scholarships.index')],
        ['icon' => 'verify', 'title' => 'पुरस्कार प्रमाणीकरण', 'en' => 'Verify an award letter online', 'url' => route('verify.award.form')],
        ['icon' => 'mail', 'title' => 'सम्पर्क तथा सहायता', 'en' => 'Contact details and help (demo information)', 'url' => '#contact'],
    ];

    $resources = [
        ['title' => 'छात्रवृत्ति सञ्चालन नीति (नमुना)', 'en' => 'Scholarship operation policy — sample document'],
        ['title' => 'आवेदन प्रपत्र तथा निर्देशिका', 'en' => 'Application form and guidance notes'],
        ['title' => 'चयन मापदण्ड प्रारूप', 'en' => 'Selection criteria template'],
        ['title' => 'पुरस्कार पत्र नमुना', 'en' => 'Award letter sample'],
    ];

    $externalLinks = [
        ['title' => 'शिक्षा, विज्ञान तथा प्रविधि मन्त्रालय', 'en' => 'Ministry of Education, Science and Technology'],
        ['title' => 'विश्वविद्यालय अनुदान आयोग', 'en' => 'University Grants Commission'],
        ['title' => 'पाठ्यक्रम विकास केन्द्र', 'en' => 'Curriculum Development Centre'],
        ['title' => 'राष्ट्रिय परीक्षा बोर्ड', 'en' => 'National Examination Board'],
    ];

    $hasFilters = $filters['q'] !== '' || $filters['level'] !== '' || $filters['grade'] !== null;
@endphp

{{-- Hero carousel --}}
<section id="top" class="relative bg-gov-dark" x-data="heroCarousel()" @mouseenter="stop()" @mouseleave="play()">
    <div class="relative h-[320px] overflow-hidden sm:h-[400px] lg:h-[460px]">
        @foreach ($slides as $i => $slide)
            <div data-slide="{{ $i }}" x-show="index === {{ $i }}" x-transition.opacity.duration.500ms class="absolute inset-0">
                <img src="{{ $slide['image'] }}" alt="{{ $slide['alt'] }}" class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-gov-dark/55 via-gov-dark/10 to-transparent"></div>

                <div class="absolute bottom-0 left-0 right-0 border-t-4 border-gov-red bg-white/90 px-5 py-4 backdrop-blur-sm sm:bottom-4 sm:left-auto sm:right-4 sm:max-w-md sm:border-l-4 sm:border-t-0 sm:px-6 sm:py-5 sm:shadow-xl">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gov-blue">{{ $slide['kicker'] }}</p>
                    <h1 class="mt-1 text-lg font-semibold leading-snug text-gov-dark sm:text-2xl">{{ $slide['title'] }}</h1>
                    <p class="mt-1 text-[10px] font-medium uppercase tracking-wide text-gov-muted">{{ $slide['en'] }}</p>
                    <p class="mt-2 text-xs leading-relaxed text-gov-ink sm:text-sm">{{ $slide['text'] }}</p>
                    <a href="{{ $slide['url'] }}" class="gov-btn gov-btn-primary mt-4 !py-2 !text-xs sm:!text-sm">
                        {{ $slide['label'] }}
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>
            </div>
        @endforeach

        <div class="absolute left-3 top-3 z-20 flex items-center gap-1.5">
            <button type="button" @click="prev()" aria-label="अघिल्लो स्लाइड"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-white/85 text-gov-dark shadow-sm transition hover:bg-white">
                <x-icon name="chevron-left" class="h-4 w-4" />
            </button>
            <button type="button" @click="next()" aria-label="अर्को स्लाइड"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-white/85 text-gov-dark shadow-sm transition hover:bg-white">
                <x-icon name="chevron-right" class="h-4 w-4" />
            </button>
            @foreach ($slides as $i => $slide)
                <button type="button" @click="go({{ $i }})" aria-label="स्लाइड {{ $i + 1 }}"
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
                <h2 class="gov-section-title">सेवाहरू तथा आवेदन प्रक्रिया</h2>
                <span class="gov-section-en">Services &amp; Application Process</span>
            </div>
            <a href="{{ route('scholarships.index') }}" class="gov-link inline-flex items-center gap-1 text-xs font-semibold">
                सबै छात्रवृत्ति हेर्नुहोस् <x-icon name="arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <a href="{{ $service['url'] }}" class="gov-card group flex items-start gap-4 p-4 transition hover:border-gov-blue/60 hover:shadow-md">
                    <span class="gov-icon-circle"><x-icon name="{{ $service['icon'] }}" class="h-5 w-5" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-gov-dark group-hover:text-gov-blue">{{ $service['title'] }}</span>
                        <span class="mt-0.5 block text-[10px] font-medium uppercase tracking-wide text-gov-muted">{{ $service['en'] }}</span>
                        <span class="mt-1.5 block text-xs leading-relaxed text-gov-muted">{{ $service['text'] }}</span>
                        <span class="gov-link mt-2 inline-flex items-center gap-1 text-xs font-semibold">
                            जानुहोस् <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                        </span>
                    </span>
                </a>
            @endforeach
        </div>

        <div class="gov-card mt-6 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-gov-dark">
                आवेदन प्रक्रिया छ चरणमा
                <span class="block text-[10px] font-medium uppercase tracking-wide text-gov-muted">Application process in six steps</span>
            </h3>
            <ol class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['१', 'दर्ता तथा प्रोफाइल', 'Registration and student profile'],
                    ['२', 'आवेदन पेश', 'Submit the application online'],
                    ['३', 'कागजात प्रमाणीकरण', 'School and local unit verification'],
                    ['४', 'चयन मूल्याङ्कन', 'Committee scoring against criteria'],
                    ['५', 'निर्णय तथा अपील', 'Decision, notice and appeal'],
                    ['६', 'पुरस्कार तथा प्रमाणीकरण', 'Award letter and QR verification'],
                ] as [$num, $title, $en])
                    <li class="flex items-start gap-3 rounded-sm border border-gov-line bg-gov-pale p-3">
                        <span class="flex h-7 w-7 flex-none items-center justify-center rounded-full bg-gov-blue text-xs font-semibold text-white">{{ $num }}</span>
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-gov-dark">{{ $title }}</span>
                            <span class="block text-[10px] uppercase tracking-wide text-gov-muted">{{ $en }}</span>
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
            <h2 class="gov-section-title">महत्त्वपूर्ण जानकारी</h2>
            <span class="gov-section-en">Important Information</span>

            <ul class="mt-6 space-y-2.5">
                @foreach ($infoLinks as $item)
                    <li>
                        <a href="{{ $item['url'] }}" class="gov-card flex items-start gap-3 p-3.5 transition hover:border-gov-blue/60 hover:shadow-sm">
                            <span class="gov-icon-circle !h-9 !w-9"><x-icon name="{{ $item['icon'] }}" class="h-4 w-4" /></span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold text-gov-dark">{{ $item['title'] }}</span>
                                    <x-icon name="arrow-right" class="h-4 w-4 flex-none text-gov-blue" />
                                </span>
                                <span class="mt-0.5 block text-[10px] uppercase tracking-wide text-gov-muted">{{ $item['en'] }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <h2 class="gov-section-title">सूचना तथा समाचार</h2>
            <span class="gov-section-en">Notices &amp; News</span>

            <ul class="mt-6 space-y-2.5">
                @forelse ($notices as $notice)
                    <li class="gov-card flex items-start gap-3 p-3.5">
                        <span class="w-20 flex-none text-xs font-medium text-gov-dark">{{ $notice['date']->format('j M Y') }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[10px] font-semibold uppercase tracking-wide text-gov-blue">{{ $notice['type'] }}</span>
                            <a href="{{ $notice['url'] }}" class="mt-0.5 block text-sm font-medium leading-snug text-gov-ink hover:text-gov-blue hover:underline">
                                {{ $notice['label'] }}
                            </a>
                        </span>
                    </li>
                @empty
                    <li class="gov-card p-4 text-sm text-gov-muted">हाल प्रकाशित सूचना उपलब्ध छैन।</li>
                @endforelse
            </ul>

            <p class="gov-meta mt-3 text-[11px]">
                * सूचनाहरू प्रकाशित छात्रवृत्तिको मिति, आवेदन खुल्ने र म्यादबाट स्वतः तयार हुन्छन्।
            </p>
        </div>
    </div>
</section>

{{-- Eligibility and scholarships --}}
<section id="scholarships" class="border-b border-gov-line bg-white py-10 sm:py-12">
    <div class="mx-auto max-w-7xl px-4">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="gov-section-title">योग्यता तथा छात्रवृत्ति</h2>
                <span class="gov-section-en">Eligibility &amp; Scholarships</span>
            </div>
            <a href="{{ route('scholarships.index') }}" class="gov-link inline-flex items-center gap-1 text-xs font-semibold">
                पूर्ण सूची हेर्नुहोस् <x-icon name="arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>

        <form method="GET" action="{{ route('home') }}" class="gov-card mt-6 p-4 sm:p-5">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="home-q" class="mb-1 block text-xs font-medium text-gov-muted">खोज / Search</label>
                    <input id="home-q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="शीर्षक, प्रदायक वा शब्द…"
                           class="gov-input" autocomplete="off">
                </div>
                <div>
                    <label for="home-level" class="mb-1 block text-xs font-medium text-gov-muted">तह / Education level</label>
                    <select id="home-level" name="level" class="gov-input">
                        <option value="">सबै तह</option>
                        @foreach ($levels as $level)
                            <option value="{{ $level->value }}" @selected($filters['level'] === $level->value)>{{ $level->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="home-grade" class="mb-1 block text-xs font-medium text-gov-muted">कक्षा / Grade</label>
                    <select id="home-grade" name="grade" class="gov-input">
                        <option value="">कुनै पनि कक्षा</option>
                        @for ($grade = 1; $grade <= 12; $grade++)
                            <option value="{{ $grade }}" @selected($filters['grade'] === $grade)>{{ $grade }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="gov-btn gov-btn-primary flex-1 lg:flex-none">
                        <x-icon name="search" class="h-4 w-4" /> खोज्नुहोस्
                    </button>
                    <a href="{{ route('home') }}" class="gov-btn gov-btn-outline">हटाउनुहोस्</a>
                </div>
            </div>
        </form>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <p class="gov-meta">
                @if ($hasFilters)
                    {{ $scholarships->count() }} वटा नतिजा भेटियो
                @else
                    हाल प्रकाशित छात्रवृत्तिहरू
                @endif
            </p>
            <p class="gov-meta">आवेदन खुला भएमा मात्र प्राथमिकता</p>
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

                    @php
                        $gradeLabel = match (true) {
                            $scholarship->target_grade_min === null && $scholarship->target_grade_max === null => 'सबै कक्षा',
                            $scholarship->target_grade_min === null => 'कक्षा '.$scholarship->target_grade_max.' सम्म',
                            $scholarship->target_grade_max === null => 'कक्षा '.$scholarship->target_grade_min.' देखि',
                            default => 'कक्षा '.$scholarship->target_grade_min.'–'.$scholarship->target_grade_max,
                        };
                    @endphp

                    <dl class="mt-3 space-y-1 text-xs text-gov-muted">
                        <div class="flex justify-between gap-2">
                            <dt>तह / Level</dt>
                            <dd class="text-right font-medium text-gov-ink">{{ $scholarship->education_level?->label() ?? 'कुनै पनि तह' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt>कक्षा / Grades</dt>
                            <dd class="text-right font-medium text-gov-ink">{{ $gradeLabel }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt>म्याद / Deadline</dt>
                            <dd class="text-right font-medium text-gov-ink">{{ $scholarship->application_deadline->format('j M Y') }}</dd>
                        </div>
                    </dl>

                    <div class="gov-divider mt-4 flex items-center justify-between gap-2 pt-3">
                        @if ($scholarship->isAcceptingApplications())
                            <x-status-badge type="success" label="आवेदन खुला" />
                        @elseif ($scholarship->isExpired())
                            <x-status-badge type="danger" label="म्याद बितेको" />
                        @else
                            <x-status-badge type="neutral" label="खुल्ने नभएको" />
                        @endif
                        <a href="{{ route('scholarships.show', $scholarship) }}" class="gov-link inline-flex items-center gap-1 text-xs font-semibold">
                            विवरण हेर्नुहोस् <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                        </a>
                    </div>
                </article>
            @empty
                <div class="gov-card col-span-full px-6 py-10 text-center">
                    <span class="gov-icon-circle mx-auto"><x-icon name="search" class="h-5 w-5" /></span>
                    <h3 class="mt-3 text-sm font-semibold text-gov-dark">मिल्दो छात्रवृत्ति भेटिएन</h3>
                    <p class="mx-auto mt-1 max-w-md text-xs leading-relaxed text-gov-muted">
                        फिल्टर हटाउनुहोस् वा अर्को शब्दले खोजी गर्नुहोस्। सबै प्रकाशित छात्रवृत्ति सार्वजनिक सूचीमा उपलब्ध छन्।
                    </p>
                    <div class="mt-4 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('home') }}" class="gov-btn gov-btn-outline">फिल्टर हटाउनुहोस्</a>
                        <a href="{{ route('scholarships.index') }}" class="gov-btn gov-btn-primary">सबै छात्रवृत्ति हेर्नुहोस्</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- Updates --}}
<section id="updates" class="border-b border-gov-line bg-gov-pale py-10 sm:py-12">
    <div class="mx-auto max-w-7xl px-4">
        <h2 class="gov-section-title">हालका अद्यावधिक</h2>
        <span class="gov-section-en">Recent Updates</span>

        <div class="mt-8 grid gap-4 lg:grid-cols-3">
            <div class="gov-card p-5 lg:col-span-2">
                <h3 class="text-sm font-semibold text-gov-dark">
                    आगामी अन्तिम मितिहरू
                    <span class="block text-[10px] font-medium uppercase tracking-wide text-gov-muted">Upcoming application deadlines</span>
                </h3>

                <ul class="mt-4 divide-y divide-gov-line">
                    @forelse ($deadlines as $scholarship)
                        <li class="flex items-center gap-3 py-3">
                            <span class="gov-icon-circle !h-9 !w-9"><x-icon name="calendar" class="h-4 w-4" /></span>
                            <span class="min-w-0 flex-1">
                                <a href="{{ route('scholarships.show', $scholarship) }}" class="block truncate text-sm font-medium text-gov-ink hover:text-gov-blue hover:underline">
                                    {{ $scholarship->title }}
                                </a>
                                <span class="gov-meta block truncate">{{ $scholarship->provider }} · {{ $scholarship->education_level?->label() ?? 'कुनै पनि तह' }}</span>
                            </span>
                            <span class="flex flex-none flex-col items-end gap-1">
                                <span class="text-xs font-semibold text-gov-dark">{{ $scholarship->application_deadline->format('j M Y') }}</span>
                                @if ($scholarship->isAcceptingApplications())
                                    <x-status-badge type="success" label="खुला" class="!text-[10px]" />
                                @else
                                    <x-status-badge type="neutral" label="खुल्ने नभएको" class="!text-[10px]" />
                                @endif
                            </span>
                        </li>
                    @empty
                        <li class="py-4 text-sm text-gov-muted">हाल आगामी म्याद भएको छात्रवृत्ति छैन।</li>
                    @endforelse
                </ul>
            </div>

            <div class="gov-card p-5">
                <h3 class="text-sm font-semibold text-gov-dark">
                    नयाँ प्रकाशन
                    <span class="block text-[10px] font-medium uppercase tracking-wide text-gov-muted">Recently published scholarships</span>
                </h3>

                <ul class="mt-4 space-y-3">
                    @forelse ($publications as $scholarship)
                        <li class="border-b border-gov-line pb-3 last:border-0 last:pb-0">
                            <span class="gov-meta block">{{ $scholarship->created_at->format('j M Y') }}</span>
                            <a href="{{ route('scholarships.show', $scholarship) }}" class="mt-0.5 block text-sm font-medium leading-snug text-gov-ink hover:text-gov-blue hover:underline">
                                {{ $scholarship->title }}
                            </a>
                        </li>
                    @empty
                        <li class="text-sm text-gov-muted">हाल नयाँ प्रकाशन छैन।</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Resources and external links --}}
<section id="resources" class="bg-white py-10 sm:py-12">
    <div class="mx-auto max-w-7xl px-4">
        <h2 class="gov-section-title">स्रोत तथा सामग्री</h2>
        <span class="gov-section-en">Resources &amp; Materials</span>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($resources as $resource)
                <div class="gov-card flex flex-col p-4">
                    <div class="flex items-start justify-between gap-2">
                        <span class="gov-icon-circle !h-9 !w-9"><x-icon name="document" class="h-4 w-4" /></span>
                        <span class="rounded-sm border border-gov-line bg-gov-pale px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-gov-muted">डेमो</span>
                    </div>
                    <h3 class="mt-3 text-sm font-semibold leading-snug text-gov-dark">{{ $resource['title'] }}</h3>
                    <p class="mt-1 text-[10px] uppercase tracking-wide text-gov-muted">{{ $resource['en'] }}</p>
                    <p class="gov-meta mt-3 border-t border-gov-line pt-2 text-[11px]">उपलब्ध छैन · डेमो सामग्री</p>
                </div>
            @endforeach
        </div>

        <div id="links" class="mt-10">
            <h3 class="text-sm font-semibold text-gov-dark">
                महत्त्वपूर्ण बाह्य लिङ्क
                <span class="block text-[10px] font-medium uppercase tracking-wide text-gov-muted">Important external links (demo placeholders)</span>
            </h3>

            <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($externalLinks as $external)
                    <li class="gov-card flex items-center gap-3 p-3.5 text-gov-muted">
                        <x-icon name="external" class="h-5 w-5 flex-none text-gov-blue/60" />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-gov-ink">{{ $external['title'] }}</span>
                            <span class="block truncate text-[10px] uppercase tracking-wide text-gov-muted">{{ $external['en'] }}</span>
                        </span>
                        <span class="rounded-sm border border-gov-line bg-gov-pale px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-gov-muted">डेमो</span>
                    </li>
                @endforeach
            </ul>

            <p class="gov-meta mt-3 text-[11px]">
                यी लिङ्कहरू डेमोका लागि मात्र देखाइएका हुन्; वास्तविक बाह्य साइटमा जोडिएका छैनन्।
            </p>
        </div>
    </div>
</section>
@endsection
