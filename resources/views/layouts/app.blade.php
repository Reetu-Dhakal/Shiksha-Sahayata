<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('nav.dashboard')) — {{ __('common.app_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded bg-blue-800 text-sm font-bold text-white">SS</span>
                <span class="text-sm font-semibold text-slate-900 sm:text-base">{{ __('common.app_name') }}</span>
            </a>

            <div class="flex items-center gap-3">
                @if (Route::has('scholarships.index'))
                    <a href="{{ route('scholarships.index') }}" class="hidden text-sm text-slate-600 hover:text-blue-800 md:block">{{ __('nav.browse') }}</a>
                @endif
                <a href="{{ route('dashboard') }}" class="hidden text-sm text-slate-600 hover:text-blue-800 md:block">{{ __('nav.dashboard') }}</a>

                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-2 rounded border border-slate-200 px-3 py-1.5 text-sm hover:bg-slate-50">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-800">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden sm:block">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-slate-400">▾</span>
                    </button>
                    <div x-show="open" x-cloak class="absolute right-0 z-20 mt-1 w-56 rounded border border-slate-200 bg-white py-1 shadow-lg" style="display: none">
                        <div class="border-b border-slate-100 px-3 py-2">
                            <p class="text-sm font-medium text-slate-800">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500">{{ auth()->user()->role->label() }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">
                                {{ __('nav.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="mx-auto flex max-w-7xl gap-6 px-4 py-6">
        <aside class="hidden w-60 shrink-0 lg:block">
            <nav class="rounded border border-slate-200 bg-white p-2">
                @include('partials.nav.'.($nav ?? match (auth()->user()->role) {
                    \App\Enums\Role::ADMIN => 'admin',
                    \App\Enums\Role::SCHOOL_OFFICER => 'school',
                    \App\Enums\Role::LOCAL_OFFICER => 'local',
                    \App\Enums\Role::COMMITTEE => 'committee',
                    \App\Enums\Role::GUARDIAN => 'guardian',
                    default => 'student',
                }))
            </nav>
        </aside>

        <div class="min-w-0 flex-1" x-data="{ mobileNav: false }">
            <div class="mb-4 rounded border border-slate-200 bg-white lg:hidden">
                <button type="button" @click="mobileNav = !mobileNav" class="flex w-full items-center justify-between px-4 py-3 text-sm font-medium">
                    <span>{{ __('nav.dashboard') }}</span>
                    <span x-text="mobileNav ? '▴' : '▾'"></span>
                </button>
                <nav class="border-t border-slate-100 p-2" x-show="mobileNav" x-cloak style="display: none">
                    @include('partials.nav.'.($nav ?? match (auth()->user()->role) {
                        \App\Enums\Role::ADMIN => 'admin',
                        \App\Enums\Role::SCHOOL_OFFICER => 'school',
                        \App\Enums\Role::LOCAL_OFFICER => 'local',
                        \App\Enums\Role::COMMITTEE => 'committee',
                        \App\Enums\Role::GUARDIAN => 'guardian',
                        default => 'student',
                    }))
                </nav>
            </div>

            <x-flash />

            @yield('content')
        </div>
    </div>
</body>
</html>
