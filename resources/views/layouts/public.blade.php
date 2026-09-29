<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('common.app_name')) — {{ __('common.tagline') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <div class="bg-blue-900 text-blue-100">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-4 py-1.5 text-xs">
            <span>{{ __('common.prototype_notice') }}</span>
            <form method="POST" action="{{ route('locale.update', app()->getLocale() === 'np' ? 'en' : 'np') }}" class="flex items-center gap-2">
                @csrf
                <label for="lang-switch" class="sr-only">{{ __('common.language') }}</label>
                <button id="lang-switch" type="submit" class="rounded border border-blue-700 px-2 py-0.5 text-blue-100 hover:bg-blue-800">
                    {{ __('common.switch_language') }}
                </button>
            </form>
        </div>
    </div>

    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded bg-blue-800 text-sm font-bold text-white">SS</span>
                <span>
                    <span class="block text-sm font-semibold leading-tight text-slate-900 sm:text-base">{{ __('common.app_name') }}</span>
                    <span class="hidden text-xs text-slate-500 sm:block">{{ __('common.tagline') }}</span>
                </span>
            </a>

            <nav class="hidden items-center gap-5 text-sm text-slate-700 md:flex">
                <a href="{{ route('home') }}" class="hover:text-blue-800">{{ __('nav.home') }}</a>
                @if (Route::has('scholarships.index'))
                    <a href="{{ route('scholarships.index') }}" class="hover:text-blue-800">{{ __('nav.browse') }}</a>
                @endif
                @if (Route::has('verify.award.form'))
                    <a href="{{ route('verify.award.form') }}" class="hover:text-blue-800">{{ __('nav.verify_award') }}</a>
                @endif
                @if (Route::has('page.appeal_info'))
                    <a href="{{ route('page.appeal_info') }}" class="hover:text-blue-800">{{ __('nav.appeal_info') }}</a>
                @endif
            </nav>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="hidden rounded border border-blue-800 px-3 py-1.5 text-sm text-blue-800 hover:bg-blue-50 sm:inline-block">
                        {{ __('nav.dashboard') }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded bg-slate-200 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-300">
                            {{ __('nav.logout') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded border border-slate-300 px-3 py-1.5 text-sm hover:bg-slate-50">{{ __('nav.login') }}</a>
                    <a href="{{ route('register') }}" class="hidden rounded bg-blue-800 px-3 py-1.5 text-sm text-white hover:bg-blue-900 sm:inline-block">{{ __('nav.register') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="mt-12 border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-6 text-xs leading-relaxed text-slate-500">
            <p>{{ __('common.footer_disclaimer') }}</p>
            <p class="mt-2">{{ __('common.governance_note') }}</p>
        </div>
    </footer>
</body>
</html>
