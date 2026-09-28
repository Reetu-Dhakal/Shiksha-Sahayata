@props(['stats' => []])

<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($stats as $stat)
        @php($tag = ! empty($stat['href']) ? 'a' : 'div')
        <{{ $tag }} @if (! empty($stat['href'])) href="{{ $stat['href'] }}" @endif
            class="rounded border border-slate-200 bg-white p-4 {{ ! empty($stat['href']) ? 'transition hover:border-blue-300 hover:bg-blue-50/40' : '' }}">
            <p class="text-xs text-slate-500">{{ $stat['label'] }}</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $stat['value'] }}</p>
        </{{ $tag }}>
    @endforeach
</div>
