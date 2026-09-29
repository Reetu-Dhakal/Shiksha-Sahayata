@extends('layouts.public')

@section('title', __('nav.login'))

@section('content')
<div class="mx-auto max-w-md px-4 py-10">
    <div class="rounded border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-lg font-semibold text-slate-900">{{ __('nav.login') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('auth.sign_in_intro') }}</p>

        <div class="mt-4">
            <x-flash />
        </div>

        <form method="POST" action="{{ route('login') }}" class="mt-2 space-y-4">
            @csrf

            <div>
                <label for="login" class="mb-1 block text-sm font-medium text-slate-700">{{ __('auth.login_field') }}</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('login') border-red-400 @enderror">
                @error('login')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">{{ __('auth.password_label') }}</label>
                <input id="password" name="password" type="password" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="rounded border-slate-300">
                {{ __('auth.remember_me') }}
            </label>

            <button type="submit"
                    class="w-full rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                {{ __('nav.login') }}
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-slate-600">
            {{ __('auth.no_account') }}
            <a href="{{ route('register') }}" class="font-medium text-blue-800 hover:underline">{{ __('nav.register') }}</a>
        </p>
    </div>
</div>
@endsection
