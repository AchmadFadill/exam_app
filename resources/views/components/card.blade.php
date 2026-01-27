@props(['title' => null, 'variant' => 'default', 'value' => null, 'subtitle' => null, 'icon' => null, 'color' => 'primary'])

@php
    $baseClasses = "bg-bg-surface dark:bg-bg-surface rounded-[2rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main overflow-hidden transition-all duration-300";
    
    // Stat Card Logic
    $colors = [
        'primary' => ['border' => 'border-l-primary', 'text' => 'text-primary', 'bg' => 'bg-primary/10'],
        'secondary' => ['border' => 'border-l-secondary', 'text' => 'text-secondary', 'bg' => 'bg-secondary/10'],
        'green' => ['border' => 'border-l-green-500', 'text' => 'text-green-600', 'bg' => 'bg-green-50'],
        'red' => ['border' => 'border-l-red-500', 'text' => 'text-red-500', 'bg' => 'bg-red-50'],
        'amber' => ['border' => 'border-l-amber-500', 'text' => 'text-amber-500', 'bg' => 'bg-amber-50'],
    ];
    $theme = $colors[$color] ?? $colors['primary'];
@endphp

@if($variant === 'stat')
    <div {{ $attributes->merge(['class' => "$baseClasses border-l-[6px] " . $theme['border'] . " p-8 flex justify-between items-center hover:scale-[1.02] active:scale-[1] transition-all duration-300 group cursor-default"]) }}>
        <div>
            <p class="text-xs font-black text-text-muted uppercase tracking-[0.2em] opacity-60">{{ $title }}</p>
            <h3 class="text-3xl font-black text-text-main mt-2 tracking-tighter">{{ $value }}</h3>
            <p class="text-[10px] font-bold mt-2 px-2 py-0.5 rounded-md inline-block {{ $theme['bg'] }} {{ $theme['text'] }} uppercase tracking-wider">
                {{ $subtitle }}
            </p>
        </div>
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-500 {{ $theme['bg'] }} {{ $theme['text'] }} group-hover:rotate-6 shadow-inner">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {{ $icon ?? '' }}
            </svg>
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => $baseClasses]) }}>
        @if($title)
        <div class="px-8 py-6 border-b border-border-subtle dark:border-border-subtle flex justify-between items-center bg-gray-50/50 dark:bg-slate-800/30">
            <h3 class="font-black text-lg text-text-main tracking-tight uppercase">{{ $title }}</h3>
            {{ $header_actions ?? '' }}
        </div>
        @endif
        
        <div class="p-8">
            {{ $slot }}
        </div>
        
        @if(isset($footer))
        <div class="bg-gray-50 dark:bg-slate-800/30 px-8 py-6 border-t border-border-subtle dark:border-border-subtle">
            {{ $footer }}
        </div>
        @endif
    </div>
@endif
