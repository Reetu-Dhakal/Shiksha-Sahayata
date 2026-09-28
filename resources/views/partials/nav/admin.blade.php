@php
    $items = [
        ['route' => 'dashboard', 'label' => __('nav.dashboard')],
        ['route' => 'admin.scholarships.index', 'label' => __('nav.scholarships')],
        ['route' => 'appeals.index', 'label' => __('nav.appeals')],
        ['route' => 'admin.schools.index', 'label' => __('nav.schools')],
        ['route' => 'admin.local-education-units.index', 'label' => __('nav.local_units')],
    ];
@endphp

@foreach ($items as $item)
    <a href="{{ route($item['route']) }}"
       class="block rounded px-3 py-2 text-sm {{ request()->routeIs($item['route']) ? 'bg-blue-50 font-medium text-blue-800' : 'text-slate-700 hover:bg-slate-50' }}">
        {{ $item['label'] }}
    </a>
@endforeach
