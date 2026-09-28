@extends('layouts.app')

@section('title', __('nav.schools'))

@section('content')
@php($editing = $school->exists)

<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">{{ $editing ? 'Edit school' : 'Add school' }}</h1>
</div>

<form method="POST" action="{{ $editing ? route('admin.schools.update', $school) : route('admin.schools.store') }}"
      class="max-w-3xl rounded border border-slate-200 bg-white p-4">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.name') }} *</label>
            <input id="name" name="name" type="text" value="{{ old('name', $school->name) }}" required
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('name') border-red-400 @enderror">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="school_code" class="mb-1 block text-sm font-medium text-slate-700">School code</label>
            <input id="school_code" name="school_code" type="text" value="{{ old('school_code', $school->school_code) }}"
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('school_code') border-red-400 @enderror">
            @error('school_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="status" class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.status') }} *</label>
            <select id="status" name="status" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                <option value="ACTIVE" @selected(old('status', $school->status) === 'ACTIVE')>Active</option>
                <option value="INACTIVE" @selected(old('status', $school->status) === 'INACTIVE')>Inactive</option>
            </select>
        </div>
        <div>
            <label for="province" class="mb-1 block text-sm font-medium text-slate-700">Province *</label>
            <input id="province" name="province" type="text" value="{{ old('province', $school->province) }}" required
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('province') border-red-400 @enderror">
            @error('province')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="district" class="mb-1 block text-sm font-medium text-slate-700">District *</label>
            <input id="district" name="district" type="text" value="{{ old('district', $school->district) }}" required
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('district') border-red-400 @enderror">
            @error('district')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="municipality" class="mb-1 block text-sm font-medium text-slate-700">Municipality *</label>
            <input id="municipality" name="municipality" type="text" value="{{ old('municipality', $school->municipality) }}" required
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('municipality') border-red-400 @enderror">
            @error('municipality')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="address" class="mb-1 block text-sm font-medium text-slate-700">Address</label>
            <input id="address" name="address" type="text" value="{{ old('address', $school->address) }}"
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
        </div>
        <div>
            <label for="contact_phone" class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.phone') }}</label>
            <input id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone', $school->contact_phone) }}"
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('contact_phone') border-red-400 @enderror">
            @error('contact_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', $school->email) }}"
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('email') border-red-400 @enderror">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="rounded bg-blue-800 px-5 py-2 text-sm font-medium text-white hover:bg-blue-900">{{ __('common.save') }}</button>
        <a href="{{ route('admin.schools.index') }}" class="rounded border border-slate-300 px-5 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
