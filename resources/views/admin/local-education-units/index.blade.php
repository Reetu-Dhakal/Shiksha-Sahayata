@extends('layouts.app')

@section('title', __('nav.local_units'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-lg font-semibold text-slate-900">{{ __('nav.local_units') }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ __('admin.local_units.index.intro') }}</p>
    </div>
    <a href="{{ route('admin.local-education-units.create') }}" class="rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">{{ __('admin.local_units.index.add') }}</a>
</div>

<form method="GET" action="{{ route('admin.local-education-units.index') }}" class="mb-4 flex gap-2">
    <label for="q" class="sr-only">{{ __('common.search') }}</label>
    <input id="q" name="q" type="search" value="{{ $search }}" placeholder="{{ __('admin.local_units.index.search_placeholder') }}"
           class="w-full max-w-sm rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
    <button type="submit" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ __('common.search') }}</button>
</form>

<div class="overflow-x-auto rounded border border-slate-200 bg-white">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">{{ __('common.name') }}</th>
                <th class="px-4 py-3">{{ __('admin.local_units.index.district') }}</th>
                <th class="px-4 py-3">{{ __('admin.local_units.index.municipality') }}</th>
                <th class="px-4 py-3">{{ __('admin.local_units.index.contact') }}</th>
                <th class="px-4 py-3">{{ __('common.status') }}</th>
                <th class="px-4 py-3">{{ __('common.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($units as $unit)
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $unit->name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $unit->district }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $unit->municipality }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $unit->contact_information ?: '—' }}</td>
                    <td class="px-4 py-3">
                        @if ($unit->isActive())
                            <x-status-badge type="success" label="{{ __('admin.active') }}" />
                        @else
                            <x-status-badge type="neutral" label="{{ __('admin.inactive') }}" />
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.local-education-units.edit', $unit) }}" class="font-medium text-blue-800 hover:underline">{{ __('common.edit') }}</a>
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

<div class="mt-4">{{ $units->links() }}</div>
@endsection
