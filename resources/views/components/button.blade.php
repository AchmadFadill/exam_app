@props(['variant' => 'primary', 'type' => 'button', 'href' => null, 'size' => 'md', 'square' => false, 'navigate' => true])

@php
    $sizes = [
        'xs' => 'px-3 py-1.5 text-[10px]',
        'sm' => 'px-4 py-2 text-xs',
        'md' => 'px-6 py-2.5 text-sm',
        'lg' => 'px-8 py-3 text-base',
    ];

    $squareSizes = [
        'xs' => 'p-1.5',
        'sm' => 'p-2',
        'md' => 'p-2.5',
        'lg' => 'p-3',
    ];

    $sizeClass = $square ? ($squareSizes[$size] ?? $squareSizes['md']) : ($sizes[$size] ?? $sizes['md']);
    
    $baseClasses = 'inline-flex items-center justify-center gap-2 border rounded-2xl font-bold transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-offset-0 disabled:opacity-50 disabled:cursor-not-allowed hover:scale-[1.02] active:scale-[0.98] hover:shadow-xl active:shadow-md group';
    
    $variants = [
        'primary' => 'border-transparent text-white bg-primary hover:brightness-110 focus:ring-primary/20 shadow-lg shadow-primary/20 hover:shadow-primary/30',
        'secondary' => 'border-border-main dark:border-border-main text-text-main bg-bg-surface dark:bg-bg-surface hover:bg-gray-50 dark:hover:bg-slate-800 focus:ring-primary/10 shadow-sm hover:shadow-md',
        'danger' => 'border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500/20 shadow-lg shadow-red-500/20 hover:shadow-red-500/30',
        'success' => 'border-transparent text-white bg-green-600 hover:bg-green-700 focus:ring-green-500/20 shadow-lg shadow-green-500/20 hover:shadow-green-500/30',
        'soft' => 'border-transparent text-primary bg-primary/10 hover:bg-primary/20 shadow-sm hover:shadow-primary/20 uppercase tracking-widest',
        'warning' => 'border-transparent text-white bg-amber-500 hover:bg-amber-600 focus:ring-amber-500/20 shadow-lg shadow-amber-500/20 hover:shadow-amber-500/30',
    ];
    
    $classes = $baseClasses . ' ' . $sizeClass . ' ' . ($variants[$variant] ?? $variants['primary']);
    $hrefString = (string) $href;
    $isInternalHref = $hrefString !== ''
        && (
            str_starts_with($hrefString, '/')
            || str_starts_with($hrefString, url('/'))
        );
    $isExcludedHref = str_starts_with($hrefString, '#')
        || str_starts_with($hrefString, 'mailto:')
        || str_starts_with($hrefString, 'tel:')
        || str_starts_with($hrefString, 'javascript:');
    $shouldNavigate = $href && $navigate && $isInternalHref && !$isExcludedHref;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes])->when($shouldNavigate, fn ($attrs) => $attrs->merge(['wire:navigate' => true])) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
