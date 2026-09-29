@extends('layouts.app')

@section('title', __('nav.audit_logs'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">{{ __('nav.audit_logs') }}</h1>
    <p class="mt-1 text-sm text-slate-600">
        {{ __('admin.audit.intro') }}
    </p>
</div>

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.audit-logs.index') }}"
           class="rounded border px-3 py-1.5 text-sm {{ $action === '' ? 'border-blue-800 bg-blue-50 text-blue-800' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">
            {{ __('common.all') }}
        </a>
        @foreach ($actions as $option)
            <a href="{{ route('admin.audit-logs.index', ['action' => $option]) }}"
               class="rounded border px-3 py-1.5 text-sm {{ $action === $option ? 'border-blue-800 bg-blue-50 text-blue-800' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">
                {{ __('admin.audit.actions')[$option] ?? $option }}
            </a>
        @endforeach
    </div>
</div>

<div class="overflow-x-auto rounded border border-slate-200 bg-white">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">{{ __('admin.audit.when') }}</th>
                <th class="px-4 py-3">{{ __('admin.audit.actor') }}</th>
                <th class="px-4 py-3">{{ __('admin.audit.action') }}</th>
                <th class="px-4 py-3">{{ __('admin.audit.description') }}</th>
                <th class="px-4 py-3">{{ __('admin.audit.ip') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($logs as $log)
                <tr>
                    <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500">{{ format_date($log->created_at, 'j M Y, H:i:s') }}</td>
                    <td class="px-4 py-3">
                        <p class="text-sm text-slate-800">{{ $log->actor?->name ?? __('admin.audit.system') }}</p>
                        <p class="text-xs text-slate-500">{{ $log->actor?->email }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="rounded border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-700">{{ __('admin.audit.actions')[$log->action] ?? $log->action }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-700">
                        {{ $log->description }}
                        @if ($log->new_values)
                            <details class="mt-1">
                                <summary class="cursor-pointer text-xs text-blue-800">{{ __('admin.audit.change_details') }}</summary>
                                <pre class="mt-1 overflow-x-auto rounded bg-slate-50 p-2 text-xs text-slate-600">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $log->ip_address ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">{{ __('admin.audit.empty') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $logs->links() }}
</div>
@endsection
