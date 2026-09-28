@extends('layouts.public')

@section('title', __('nav.register'))

@section('content')
<div class="mx-auto max-w-lg px-4 py-10">
    <div class="rounded border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-lg font-semibold text-slate-900">Create your account</h1>
        <p class="mt-1 text-sm text-slate-500">
            Register as a student or as a guardian. School, local education and committee accounts are created by the administrator.
        </p>

        <div class="mt-4">
            <x-flash />
        </div>

        <form method="POST" action="{{ route('register') }}" class="mt-2 space-y-4" x-data="{ type: '{{ old('account_type', 'student') }}' }">
            @csrf

            <fieldset>
                <legend class="mb-1 block text-sm font-medium text-slate-700">Account type</legend>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex cursor-pointer items-start gap-2 rounded border p-3 text-sm"
                           :class="type === 'student' ? 'border-blue-600 bg-blue-50' : 'border-slate-300'">
                        <input type="radio" name="account_type" value="student" x-model="type" @checked(old('account_type', 'student') === 'student') class="mt-0.5">
                        <span>
                            <span class="block font-medium text-slate-800">Student</span>
                            <span class="block text-xs text-slate-500">I am the student applying for scholarships.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-2 rounded border p-3 text-sm"
                           :class="type === 'guardian' ? 'border-blue-600 bg-blue-50' : 'border-slate-300'">
                        <input type="radio" name="account_type" value="guardian" x-model="type" @checked(old('account_type') === 'guardian') class="mt-0.5">
                        <span>
                            <span class="block font-medium text-slate-800">Guardian</span>
                            <span class="block text-xs text-slate-500">I am a parent or guardian of the student.</span>
                        </span>
                    </label>
                </div>
                @error('account_type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </fieldset>

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email <span class="font-normal text-slate-400">(optional)</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">Phone <span class="font-normal text-slate-400">(optional)</span></label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}" placeholder="98XXXXXXXX"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('phone') border-red-400 @enderror">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="-mt-2 text-xs text-slate-500">At least one of email or phone is required — either can be used to sign in.</p>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('password') border-red-400 @enderror">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                </div>
            </div>

            <button type="submit"
                    class="w-full rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                {{ __('nav.register') }}
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-slate-600">
            Already registered?
            <a href="{{ route('login') }}" class="font-medium text-blue-800 hover:underline">{{ __('nav.login') }}</a>
        </p>
    </div>
</div>
@endsection
