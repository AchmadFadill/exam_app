@props(['href', 'active' => false])

@php
$classes = $active
            ? 'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors bg-blue-900 text-white'
            : 'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors text-blue-100 hover:bg-blue-800 hover:text-white';

$iconClasses = $active
            ? 'w-5 h-5 text-secondary'
            : 'w-5 h-5 text-blue-300';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    <svg class="{{ $iconClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {{ $icon ?? '' }}
    </svg>
    {{ $slot }}
</a>
