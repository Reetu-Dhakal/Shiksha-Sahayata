@props(['label' => null, 'type' => 'neutral'])

@php
    $styles = [
        'neutral' => 'bg-slate-100 text-slate-700 border-slate-200',
        'info' => 'bg-blue-50 text-blue-800 border-blue-200',
        'success' => 'bg-green-50 text-green-800 border-green-200',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
        'danger' => 'bg-red-50 text-red-700 border-red-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded border px-2 py-0.5 text-xs font-medium '.($styles[$type] ?? $styles['neutral'])]) }}>
    {{ $label }}
</span>
