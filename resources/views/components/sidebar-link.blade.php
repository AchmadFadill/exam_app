@props(['href', 'active' => false, 'navigate' => true])

@php
$classes = $active
            ? 'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 bg-white/10 text-white font-bold shadow-sm active:scale-[0.98]'
            : 'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-white/70 hover:bg-white/5 hover:text-white font-medium active:scale-[0.98]';

$iconClasses = $active
            ? 'w-5 h-5 text-secondary transition-colors duration-200'
            : 'w-5 h-5 text-white/40 group-hover:text-white transition-colors duration-200';
    $isInternal = str_starts_with((string) $href, '/')
        || str_starts_with((string) $href, url('/'))
        || str_starts_with((string) $href, route('login'));

    $shouldNavigate = $navigate && $isInternal && !str_starts_with((string) $href, '#');
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "$classes group"])->when($shouldNavigate, fn ($attrs) => $attrs->merge(['wire:navigate' => true])) }}>
    <svg class="{{ $iconClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {{ $icon ?? '' }}
    </svg>
    {{ $slot }}
</a>
