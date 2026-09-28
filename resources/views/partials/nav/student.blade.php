@php
    $items = [
        ['route' => 'dashboard', 'label' => __('nav.dashboard')],
        ['route' => 'applications.index', 'label' => __('nav.applications')],
        ['route' => 'awards.index', 'label' => __('nav.awards')],
        ['route' => 'profile.show', 'label' => __('nav.profile')],
    ];
@endphp

@foreach ($items as $item)
    <a href="{{ route($item['route']) }}"
       class="block rounded px-3 py-2 text-sm {{ request()->routeIs($item['route']) ? 'bg-blue-50 font-medium text-blue-800' : 'text-slate-700 hover:bg-slate-50' }}">
        {{ $item['label'] }}
    </a>
@endforeach
