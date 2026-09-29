<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('common.app_name')) — {{ __('common.tagline') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="gov-body min-h-screen bg-white">
    {{-- Government-style institutional header --}}
    <header class="bg-gov-dark text-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-6 gap-y-3 px-4 py-3 sm:h-24 sm:flex-nowrap sm:py-0">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/emblem-placeholder.svg') }}" alt="Shiksha Sahayata emblem placeholder"
                     class="h-12 w-12 flex-none rounded-full bg-white/95 p-1 sm:h-14 sm:w-14">
                <span class="leading-tight">
                    <span class="block text-[11px] tracking-wide text-blue-200">नेपाल सरकार · Government of Nepal</span>
                    <span class="block text-xs font-semibold text-white sm:text-sm">शिक्षा, विज्ञान तथा प्रविधि मन्त्रालय</span>
                    <span class="block text-[10px] text-blue-200">Ministry of Education, Science and Technology</span>
                    <span class="mt-1.5 flex flex-wrap items-center gap-2">
                        <span class="text-sm font-semibold tracking-wide sm:text-base">Shiksha Sahayata</span>
                        <span class="rounded-sm bg-gov-red px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide">शैक्षिक प्रोटोटाइप</span>
                    </span>
                </span>
            </a>

            <p class="hidden max-w-xs text-center text-xs leading-relaxed text-blue-200 xl:block">
                छात्रवृत्ति सेवा सम्बन्धी डिजिटल प्रणाली<br>
                <span class="text-[10px] uppercase tracking-wider">Digital Scholarship Management and Student Assistance System</span>
            </p>

            <div class="flex items-center gap-3">
                <div class="hidden text-right text-[10px] leading-tight text-blue-200 sm:block">
                    <span class="block text-xs font-medium text-white">{{ now()->format('j M Y') }}</span>
                    <span>आजको मिति / Today</span>
                </div>
                <img src="{{ asset('images/flag-placeholder.svg') }}" alt="Nepal-inspired flag placeholder" class="hidden h-9 w-auto sm:block">
                <form method="POST" action="{{ route('locale.update', app()->getLocale() === 'np' ? 'en' : 'np') }}">
                    @csrf
                    <button type="submit" class="rounded-sm border border-white/30 px-2 py-1 text-[11px] text-blue-100 transition hover:bg-white/10">
                        {{ app()->getLocale() === 'np' ? 'English' : 'नेपाली' }}
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Main navigation --}}
    <nav class="border-b border-gov-line bg-white" x-data="{ open: false }">
        <div class="mx-auto flex h-12 max-w-7xl items-center justify-between px-4">
            <div class="hidden h-full items-center lg:flex">
                <a href="{{ route('home') }}" class="gov-nav-link gov-nav-link-active">गृहपृष्ठ</a>
                <a href="{{ route('scholarships.index') }}" class="gov-nav-link">छात्रवृत्ति</a>
                <a href="{{ route('home') }}#services" class="gov-nav-link">आवेदन प्रक्रिया</a>
                <a href="{{ route('home') }}#scholarships" class="gov-nav-link">योग्यता</a>
                <a href="{{ route('home') }}#notices" class="gov-nav-link">सूचना</a>
                <a href="{{ route('home') }}#resources" class="gov-nav-link">स्रोत तथा सामग्री</a>
                <a href="{{ route('home') }}#about" class="gov-nav-link">हाम्रो बारेमा</a>
            </div>

            <div class="hidden items-center gap-2 lg:flex">
                <form method="GET" action="{{ route('scholarships.index') }}" class="flex items-center">
                    <label for="nav-search" class="sr-only">खोज्नुहोस्</label>
                    <input id="nav-search" name="q" type="search" placeholder="छात्रवृत्ति खोज्नुहोस्…"
                           class="h-8 w-44 rounded-l-sm border border-gov-line bg-gov-pale px-2.5 text-xs text-gov-ink placeholder:text-gov-muted focus:border-gov-blue focus:outline-none">
                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-r-sm bg-gov-blue text-white transition hover:bg-gov-dark" aria-label="खोज्नुहोस्">
                        <x-icon name="search" class="h-4 w-4" />
                    </button>
                </form>

                @auth
                    <a href="{{ route('dashboard') }}" class="gov-btn gov-btn-outline !px-2.5 !py-1 !text-xs">ड्यासबोर्ड</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="gov-btn !px-2.5 !py-1 !text-xs text-gov-muted hover:text-gov-dark">लगआउट</button>
                    </form>
                @else
                    <a href="{{ route('register') }}" class="gov-btn gov-btn-outline !px-2.5 !py-1 !text-xs">दर्ता</a>
                    <a href="{{ route('login') }}" class="gov-btn gov-btn-primary !px-3 !py-1 !text-xs">लगइन</a>
                @endauth
            </div>

            <button type="button" class="flex h-9 w-9 items-center justify-center rounded-sm border border-gov-line text-gov-dark lg:hidden"
                    @click="open = !open" :aria-expanded="open" aria-label="मेनु">
                <x-icon name="close" x-show="open" x-cloak class="h-5 w-5" />
                <x-icon name="menu" x-show="!open" class="h-5 w-5" />
            </button>
        </div>

        <div class="border-t border-gov-line bg-gov-pale lg:hidden" x-show="open" x-cloak x-transition.opacity.duration.150ms>
            <div class="mx-auto max-w-7xl space-y-1 px-4 py-3">
                <a href="{{ route('home') }}" class="block rounded-sm px-2 py-2 text-sm font-medium text-gov-dark hover:bg-gov-light">गृहपृष्ठ</a>
                <a href="{{ route('scholarships.index') }}" class="block rounded-sm px-2 py-2 text-sm font-medium text-gov-dark hover:bg-gov-light">छात्रवृत्ति</a>
                <a href="{{ route('home') }}#services" class="block rounded-sm px-2 py-2 text-sm font-medium text-gov-dark hover:bg-gov-light">आवेदन प्रक्रिया</a>
                <a href="{{ route('home') }}#scholarships" class="block rounded-sm px-2 py-2 text-sm font-medium text-gov-dark hover:bg-gov-light">योग्यता</a>
                <a href="{{ route('home') }}#notices" class="block rounded-sm px-2 py-2 text-sm font-medium text-gov-dark hover:bg-gov-light">सूचना</a>
                <a href="{{ route('home') }}#resources" class="block rounded-sm px-2 py-2 text-sm font-medium text-gov-dark hover:bg-gov-light">स्रोत तथा सामग्री</a>
                <a href="{{ route('home') }}#about" class="block rounded-sm px-2 py-2 text-sm font-medium text-gov-dark hover:bg-gov-light">हाम्रो बारेमा</a>

                <form method="GET" action="{{ route('scholarships.index') }}" class="flex items-center pt-2">
                    <label for="nav-search-mobile" class="sr-only">खोज्नुहोस्</label>
                    <input id="nav-search-mobile" name="q" type="search" placeholder="छात्रवृत्ति खोज्नुहोस्…"
                           class="h-9 flex-1 rounded-l-sm border border-gov-line bg-white px-3 text-sm text-gov-ink placeholder:text-gov-muted focus:border-gov-blue focus:outline-none">
                    <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-r-sm bg-gov-blue text-white" aria-label="खोज्नुहोस्">
                        <x-icon name="search" class="h-4 w-4" />
                    </button>
                </form>

                <div class="flex flex-wrap items-center gap-2 pt-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="gov-btn gov-btn-outline !py-1.5 !text-xs">ड्यासबोर्ड</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="gov-btn !py-1.5 !text-xs text-gov-muted">लगआउट</button>
                        </form>
                    @else
                        <a href="{{ route('register') }}" class="gov-btn gov-btn-outline !py-1.5 !text-xs">दर्ता</a>
                        <a href="{{ route('login') }}" class="gov-btn gov-btn-primary !py-1.5 !text-xs">लगइन</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Announcement / information strip --}}
    <div class="border-b border-gov-line bg-gov-light">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-3 gap-y-1 px-4 py-2">
            <span class="rounded-sm bg-gov-red px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">सूचना</span>
            <span class="text-xs font-medium text-gov-ink">
                @if (($openCount ?? 0) > 0)
                    नयाँ छात्रवृत्ति आवेदनहरू उपलब्ध छन्।
                    <span class="text-gov-muted font-normal">({{ $openCount }} वटा आवेदनका लागि खुला)</span>
                @else
                    हाल आवेदनका लागि खुला गरिएको छात्रवृत्ति छैन।
                @endif
            </span>
            <a href="{{ route('scholarships.index', ['open' => 1]) }}" class="gov-link ml-auto hidden items-center gap-1 text-xs font-semibold sm:inline-flex">
                विवरण हेर्नुहोस् <x-icon name="arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>
    </div>

    <main>
        @yield('content')
    </main>

    {{-- Government-style footer --}}
    <footer id="about" class="mt-12 bg-gov-dark text-blue-100">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/emblem-placeholder.svg') }}" alt="Shiksha Sahayata emblem placeholder" class="h-11 w-11 rounded-full bg-white/95 p-1">
                    <div class="leading-tight">
                        <p class="text-sm font-semibold text-white">Shiksha Sahayata</p>
                        <p class="text-[10px] uppercase tracking-wide text-blue-200">शिक्षा सहायता</p>
                    </div>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-blue-200">
                    डिजिटल छात्रवृत्ति व्यवस्थापन तथा विद्यार्थी सहायता प्रणाली<br>
                    <span class="text-[11px]">Digital Scholarship Management and Student Assistance System</span>
                </p>
                <p class="mt-3 rounded-sm border border-white/15 bg-white/5 px-2 py-1.5 text-[10px] leading-relaxed text-blue-200">
                    {{ __('common.prototype_notice') }}
                </p>
            </div>

            <div id="contact">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-white">सम्पर्क <span class="font-normal normal-case tracking-normal text-blue-200">/ Contact</span></h2>
                <ul class="mt-3 space-y-2 text-xs text-blue-200">
                    <li class="flex items-start gap-2">
                        <x-icon name="mail" class="mt-0.5 h-4 w-4 flex-none text-blue-300" />
                        <span>info@shikshasahayata.example<br><span class="text-[10px] text-blue-300">(काल्पनिक डेमो इमेल)</span></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="phone" class="mt-0.5 h-4 w-4 flex-none text-blue-300" />
                        <span>+977-1-0000000<br><span class="text-[10px] text-blue-300">(काल्पनिक डेमो फोन)</span></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="pin" class="mt-0.5 h-4 w-4 flex-none text-blue-300" />
                        <span>काठमाडौं, नेपाल<br><span class="text-[10px] text-blue-300">(काल्पनिक डेमो ठेगाना)</span></span>
                    </li>
                </ul>
            </div>

            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wider text-white">महत्त्वपूर्ण लिङ्क <span class="font-normal normal-case tracking-normal text-blue-200">/ Important Links</span></h2>
                <ul class="mt-3 space-y-1.5 text-xs">
                    <li><a href="{{ route('home') }}#about" class="inline-flex items-center gap-1.5 text-blue-200 hover:text-white">परिचय / About</a></li>
                    <li><a href="{{ route('home') }}#contact" class="inline-flex items-center gap-1.5 text-blue-200 hover:text-white">सम्पर्क / Contact</a></li>
                    <li><a href="{{ route('scholarships.index') }}" class="inline-flex items-center gap-1.5 text-blue-200 hover:text-white">छात्रवृत्ति सूची / Scholarships</a></li>
                    <li><a href="{{ route('verify.award.form') }}" class="inline-flex items-center gap-1.5 text-blue-200 hover:text-white">पुरस्कार प्रमाणीकरण / Verify Award</a></li>
                    <li class="inline-flex items-center gap-1.5 text-blue-300">गोपनीयता नीति / Privacy Policy <span class="rounded-sm border border-white/20 px-1 py-0.5 text-[9px] uppercase">डेमो</span></li>
                    <li class="inline-flex items-center gap-1.5 text-blue-300">सर्तहरू / Terms <span class="rounded-sm border border-white/20 px-1 py-0.5 text-[9px] uppercase">डेमो</span></li>
                </ul>
            </div>

            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wider text-white">सेवाहरू <span class="font-normal normal-case tracking-normal text-blue-200">/ Services</span></h2>
                <ul class="mt-3 space-y-1.5 text-xs">
                    <li><a href="{{ route('scholarships.index') }}" class="text-blue-200 hover:text-white">छात्रवृत्ति खोज्नुहोस् / Find Scholarship</a></li>
                    <li><a href="{{ $applyUrl }}" class="text-blue-200 hover:text-white">आवेदन गर्नुहोस् / Apply</a></li>
                    <li><a href="{{ $trackUrl }}" class="text-blue-200 hover:text-white">आवेदन स्थिति / Track Application</a></li>
                    <li><a href="{{ route('verify.award.form') }}" class="text-blue-200 hover:text-white">पुरस्कार प्रमाणीकरण / Verify Award</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-4 text-[11px] text-blue-200 sm:flex-row sm:items-center sm:justify-between">
                <p>© 2026 Shiksha Sahayata · BSc CSIT Academic Project</p>
                <p>{{ __('common.governance_note') }}</p>
            </div>
        </div>
    </footer>
</body>
</html>
