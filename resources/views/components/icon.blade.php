@props(['name', 'label' => null])

@php
    $paths = [
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20.5 20.5-4-4"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'arrow-right' => '<path d="M4 12h15"/><path d="m13 6 6 6-6 6"/>',
        'chevron-left' => '<path d="m14 6-6 6 6 6"/>',
        'chevron-right' => '<path d="m10 6 6 6-6 6"/>',
        'document' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h4"/>',
        'bell' => '<path d="M18 16v-5a6 6 0 1 0-12 0v5l-2 2h16z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'graduation' => '<path d="m3 9 9-4 9 4-9 4z"/><path d="M7 11.5V16c0 1.4 2.2 2.5 5 2.5s5-1.1 5-2.5v-4.5"/><path d="M21 9v5"/>',
        'apply' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'track' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'checklist' => '<path d="M10 6h10M10 12h10M10 18h10"/><path d="m3 6 1.6 1.6L7.6 4.6"/><path d="m3 12 1.6 1.6 3-3"/><path d="m3 18 1.6 1.6 3-3"/>',
        'verify' => '<path d="M12 3 5 6v5c0 4.4 2.9 7.9 7 10 4.1-2.1 7-5.6 7-10V6z"/><path d="m9 12 2.2 2.2L15.5 10"/>',
        'external' => '<path d="M14 4h6v6"/><path d="M20 4 11 13"/><path d="M18 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 7.5 8.5 6 8.5-6"/>',
        'phone' => '<path d="M6 3h4l2 5-2.5 1.5a12 12 0 0 0 5 5L16 12l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 4 5a2 2 0 0 1 2-2z"/>',
        'pin' => '<path d="M12 21s-7-5.6-7-11a7 7 0 1 1 14 0c0 5.4-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 7.6h.01"/>',
        'link' => '<path d="M10.5 13.5a4.5 4.5 0 0 0 6.6.4l2-2a4.5 4.5 0 0 0-6.4-6.4l-1.1 1.1"/><path d="M13.5 10.5a4.5 4.5 0 0 0-6.6-.4l-2 2a4.5 4.5 0 0 0 6.4 6.4l1.1-1.1"/>',
    ];

    $svg = $attributes->merge([
        'fill' => 'none',
        'stroke' => 'currentColor',
        'stroke-width' => '1.75',
        'stroke-linecap' => 'round',
        'stroke-linejoin' => 'round',
        'viewBox' => '0 0 24 24',
    ]);

    if (! $attributes->has('class')) {
        $svg = $svg->class('h-5 w-5');
    }
@endphp

<svg {{ $svg }} @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif>
    {!! $paths[$name] ?? $paths['info'] !!}
</svg>
