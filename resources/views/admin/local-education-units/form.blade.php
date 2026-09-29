@extends('layouts.app')

@section('title', __('nav.local_units'))

@section('content')
@php($editing = $unit->exists)

<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">{{ $editing ? __('admin.local_units.form.edit') : __('admin.local_units.form.add') }}</h1>
</div>

<form method="POST" action="{{ $editing ? route('admin.local-education-units.update', $unit) : route('admin.local-education-units.store') }}"
      class="max-w-3xl rounded border border-slate-200 bg-white p-4">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.name') }} *</label>
            <input id="name" name="name" type="text" value="{{ old('name', $unit->name) }}" required
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('name') border-red-400 @enderror">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="province" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.local_units.form.province') }} *</label>
            <input id="province" name="province" type="text" value="{{ old('province', $unit->province) }}" required
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('province') border-red-400 @enderror">
            @error('province')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="district" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.local_units.form.district') }} *</label>
            <input id="district" name="district" type="text" value="{{ old('district', $unit->district) }}" required
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('district') border-red-400 @enderror">
            @error('district')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="municipality" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.local_units.form.municipality') }} *</label>
            <input id="municipality" name="municipality" type="text" value="{{ old('municipality', $unit->municipality) }}" required
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('municipality') border-red-400 @enderror">
            @error('municipality')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="contact_information" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.local_units.form.contact_information') }}</label>
            <input id="contact_information" name="contact_information" type="text" value="{{ old('contact_information', $unit->contact_information) }}"
                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
        </div>
        <div>
            <label for="status" class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.status') }} *</label>
            <select id="status" name="status" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                <option value="ACTIVE" @selected(old('status', $unit->status) === 'ACTIVE')>{{ __('admin.active') }}</option>
                <option value="INACTIVE" @selected(old('status', $unit->status) === 'INACTIVE')>{{ __('admin.inactive') }}</option>
            </select>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="rounded bg-blue-800 px-5 py-2 text-sm font-medium text-white hover:bg-blue-900">{{ __('common.save') }}</button>
        <a href="{{ route('admin.local-education-units.index') }}" class="rounded border border-slate-300 px-5 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
