@extends('layouts.app')

@section('title', __('nav.schools'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-lg font-semibold text-slate-900">{{ __('nav.schools') }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ __('admin.schools.index.intro') }}</p>
    </div>
    <a href="{{ route('admin.schools.create') }}" class="rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">{{ __('admin.schools.index.add') }}</a>
</div>

<form method="GET" action="{{ route('admin.schools.index') }}" class="mb-4 flex gap-2">
    <label for="q" class="sr-only">{{ __('common.search') }}</label>
    <input id="q" name="q" type="search" value="{{ $search }}" placeholder="{{ __('admin.schools.index.search_placeholder') }}"
           class="w-full max-w-sm rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
    <button type="submit" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ __('common.search') }}</button>
</form>

<div class="overflow-x-auto rounded border border-slate-200 bg-white">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">{{ __('common.name') }}</th>
                <th class="px-4 py-3">{{ __('admin.schools.index.code') }}</th>
                <th class="px-4 py-3">{{ __('admin.schools.index.district') }}</th>
                <th class="px-4 py-3">{{ __('admin.schools.index.municipality') }}</th>
                <th class="px-4 py-3">{{ __('common.status') }}</th>
                <th class="px-4 py-3">{{ __('common.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($schools as $school)
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $school->name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $school->school_code ?: '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $school->district }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $school->municipality }}</td>
                    <td class="px-4 py-3">
                        @if ($school->isActive())
                            <x-status-badge type="success" label="{{ __('admin.active') }}" />
                        @else
                            <x-status-badge type="neutral" label="{{ __('admin.inactive') }}" />
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.schools.edit', $school) }}" class="font-medium text-blue-800 hover:underline">{{ __('common.edit') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">{{ __('common.no_records') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $schools->links() }}</div>
@endsection
