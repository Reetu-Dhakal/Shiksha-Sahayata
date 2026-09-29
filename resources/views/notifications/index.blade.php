@extends('layouts.app')

@section('title', __('nav.notifications'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-lg font-semibold text-slate-900">{{ __('nav.notifications') }}</h1>
        <p class="mt-1 text-sm text-slate-600">
            {{ __('activity.index.intro') }}
            @if ($unreadCount > 0)
                <span class="font-medium text-blue-800">{{ __('activity.index.unread_count', ['count' => $unreadCount]) }}</span>
            @endif
        </p>
    </div>

    @if ($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="rounded border border-blue-800 px-3 py-1.5 text-sm font-medium text-blue-800 hover:bg-blue-50">
                {{ __('activity.index.mark_all_read') }}
            </button>
        </form>
    @endif
</div>

@if ($notifications->isEmpty())
    <x-empty-state :title="__('activity.index.empty_title')" :message="__('activity.index.empty_message')" />
@else
    <div class="space-y-3">
        @foreach ($notifications as $notification)
            <article class="rounded border bg-white p-4 {{ $notification->isUnread() ? 'border-blue-200 bg-blue-50/40' : 'border-slate-200' }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <x-status-badge :type="$notification->type->badgeType()" :label="$notification->title()" />
                            @if ($notification->isUnread())
                                <span class="text-xs font-medium text-blue-800">{{ __('activity.index.unread') }}</span>
                            @endif
                        </div>
                        <p class="mt-2 text-sm text-slate-700">{{ $notification->body() }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ format_date($notification->created_at, 'j M Y, H:i') }}</p>
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        @if ($notification->link)
                            <a href="{{ $notification->link }}" class="text-xs font-medium text-blue-800 hover:underline">{{ __('activity.index.open') }}</a>
                        @endif
                        @if ($notification->isUnread())
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf
                                <button type="submit" class="text-xs text-slate-500 hover:text-slate-700">{{ __('activity.index.mark_read') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection
