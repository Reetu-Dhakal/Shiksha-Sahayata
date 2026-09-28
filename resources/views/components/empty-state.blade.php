@props(['title', 'message' => null, 'icon' => null])

<div class="rounded border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
    @if ($icon)
        <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">{{ $icon }}</div>
    @endif
    <h3 class="text-sm font-medium text-slate-700">{{ $title }}</h3>
    @if ($message)
        <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">{{ $message }}</p>
    @endif
    <div class="mt-4">{{ $slot }}</div>
</div>
