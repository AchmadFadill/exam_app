@props(['variant' => 'primary', 'type' => 'button', 'href' => null])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 px-6 py-2.5 border rounded-2xl font-bold transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-offset-0 disabled:opacity-50 disabled:cursor-not-allowed hover:scale-[1.02] active:scale-[0.98] hover:shadow-xl active:shadow-md group';
    
    $variants = [
        'primary' => 'border-transparent text-white bg-primary hover:brightness-110 focus:ring-primary/20 shadow-lg shadow-primary/20 hover:shadow-primary/30',
        'secondary' => 'border-border-main dark:border-border-main text-text-main bg-bg-surface dark:bg-bg-surface hover:bg-gray-50 dark:hover:bg-slate-800 focus:ring-primary/10 shadow-sm hover:shadow-md',
        'danger' => 'border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500/20 shadow-lg shadow-red-500/20 hover:shadow-red-500/30',
        'success' => 'border-transparent text-white bg-green-600 hover:bg-green-700 focus:ring-green-500/20 shadow-lg shadow-green-500/20 hover:shadow-green-500/30',
        'soft' => 'border-transparent text-primary bg-primary/10 hover:bg-primary/20 shadow-sm hover:shadow-primary/20 uppercase tracking-widest text-[10px] sm:text-xs',
    ];
    
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
