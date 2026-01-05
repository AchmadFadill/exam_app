@props(['title' => null, 'variant' => 'default', 'value' => null, 'subtitle' => null, 'icon' => null, 'color' => 'primary'])

@php
    $baseClasses = "bg-bg-surface rounded-xl shadow-sm border border-gray-100 overflow-hidden";
    
    // Stat Card Logic
    $colors = [
        'primary' => ['border' => 'border-l-primary', 'text' => 'text-primary', 'bg' => 'bg-blue-50'],
        'secondary' => ['border' => 'border-l-secondary', 'text' => 'text-secondary', 'bg' => 'bg-amber-50'],
        'green' => ['border' => 'border-l-green-500', 'text' => 'text-green-600', 'bg' => 'bg-green-50'],
        'red' => ['border' => 'border-l-red-500', 'text' => 'text-red-500', 'bg' => 'bg-red-50'],
        'amber' => ['border' => 'border-l-amber-500', 'text' => 'text-amber-500', 'bg' => 'bg-amber-50'],
    ];
    $theme = $colors[$color] ?? $colors['primary'];
@endphp

@if($variant === 'stat')
    <div {{ $attributes->merge(['class' => "$baseClasses border-l-4 " . $theme['border'] . " p-6 flex justify-between items-center hover:shadow-md transition-shadow"]) }}>
        <div>
            <p class="text-sm font-medium text-text-muted">{{ $title }}</p>
            <h3 class="text-2xl font-bold text-text-main mt-1">{{ $value }}</h3>
            <p class="text-xs font-medium mt-1 {{ $theme['text'] }}">
                {{ $subtitle }}
            </p>
        </div>
        <div class="p-3 rounded-full {{ $theme['bg'] }} {{ $theme['text'] }}">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {{ $icon ?? '' }}
            </svg>
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => $baseClasses]) }}>
        @if($title)
        <div class="p-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-semibold text-text-main">{{ $title }}</h3>
            {{ $header_actions ?? '' }}
        </div>
        @endif
        
        <div class="p-6">
            {{ $slot }}
        </div>
        
        @if(isset($footer))
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
            {{ $footer }}
        </div>
        @endif
    </div>
@endif
