@extends('layouts.app')

@section('title', __('nav.scholarships'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-lg font-semibold text-slate-900">Scholarship Management</h1>
        <p class="mt-1 text-sm text-slate-600">Create, configure and publish scholarships.</p>
    </div>
    <a href="{{ route('admin.scholarships.create') }}" class="rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">
        {{ __('nav.create_scholarship') }}
    </a>
</div>

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.scholarships.index') }}"
           class="rounded border px-3 py-1.5 text-sm {{ $status === '' ? 'border-blue-800 bg-blue-50 text-blue-800' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">
            {{ __('common.all') }}
        </a>
        @foreach ($statuses as $option)
            <a href="{{ route('admin.scholarships.index', ['status' => $option->value]) }}"
               class="rounded border px-3 py-1.5 text-sm {{ $status === $option->value ? 'border-blue-800 bg-blue-50 text-blue-800' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">
                {{ $option->label() }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.scholarships.index') }}" class="flex gap-2">
        @if ($status !== '')
            <input type="hidden" name="status" value="{{ $status }}">
        @endif
        <label for="q" class="sr-only">{{ __('common.search') }}</label>
        <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Search title or provider"
               class="w-full max-w-xs rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
        <button type="submit" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ __('common.search') }}</button>
    </form>
</div>

<div class="overflow-x-auto rounded border border-slate-200 bg-white">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Scholarship</th>
                <th class="px-4 py-3">Application window</th>
                <th class="px-4 py-3">Slots</th>
                <th class="px-4 py-3">{{ __('common.status') }}</th>
                <th class="px-4 py-3">{{ __('common.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($scholarships as $scholarship)
                <tr>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800">{{ $scholarship->title }}</p>
                        <p class="text-xs text-slate-500">{{ $scholarship->provider }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $scholarship->application_start->format('Y-m-d') }} → {{ $scholarship->application_deadline->format('Y-m-d') }}
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $scholarship->available_slots ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <x-status-badge :type="$scholarship->status->badgeType()" :label="$scholarship->status->label()" />
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('admin.scholarships.edit', $scholarship) }}" class="font-medium text-blue-800 hover:underline">{{ __('common.edit') }}</a>

                            @if ($scholarship->status === \App\Enums\ScholarshipStatus::DRAFT || $scholarship->status === \App\Enums\ScholarshipStatus::CLOSED)
                                <form method="POST" action="{{ route('admin.scholarships.status', $scholarship) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="PUBLISHED">
                                    <button type="submit" class="text-xs font-medium text-green-700 hover:underline">Publish</button>
                                </form>
                            @endif

                            @if ($scholarship->status === \App\Enums\ScholarshipStatus::PUBLISHED)
                                <form method="POST" action="{{ route('admin.scholarships.status', $scholarship) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="CLOSED">
                                    <button type="submit" class="text-xs font-medium text-amber-700 hover:underline">Close</button>
                                </form>
                            @endif

                            @if ($scholarship->status === \App\Enums\ScholarshipStatus::PUBLISHED || $scholarship->status === \App\Enums\ScholarshipStatus::CLOSED)
                                <form method="POST" action="{{ route('admin.scholarships.status', $scholarship) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="COMPLETED">
                                    <button type="submit" class="text-xs font-medium text-slate-600 hover:underline">Mark completed</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                        No scholarships yet. Create the first scholarship to get started.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $scholarships->links() }}</div>
@endsection
