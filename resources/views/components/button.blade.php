@props(['variant' => 'primary', 'type' => 'button', 'href' => null])

@php
    $baseClasses = 'inline-flex items-center justify-center px-4 py-2 border rounded-lg font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variants = [
        'primary' => 'border-transparent text-white bg-primary hover:bg-blue-700 focus:ring-primary',
        'secondary' => 'border-gray-300 text-text-main bg-white hover:bg-gray-50 focus:ring-primary',
        'danger' => 'border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500',
        'success' => 'border-transparent text-white bg-green-600 hover:bg-green-700 focus:ring-green-500',
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
