@props(['href', 'active' => false])

@php
$classes = $active
            ? 'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 bg-white/10 text-white font-bold shadow-sm'
            : 'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-white/70 hover:bg-white/5 hover:text-white font-medium';

$iconClasses = $active
            ? 'w-5 h-5 text-secondary transition-colors duration-200'
            : 'w-5 h-5 text-white/40 group-hover:text-white transition-colors duration-200';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "$classes group"]) }}>
    <svg class="{{ $iconClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {{ $icon ?? '' }}
    </svg>
    {{ $slot }}
</a>
