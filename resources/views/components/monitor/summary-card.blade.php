@props([
    'label',
    'value',
    'variant' => 'default'
])

@php
    $valueClass = match($variant) {
        'primary' => 'text-primary',
        'success' => 'text-green-600',
        'gray' => 'text-gray-400',
        default => 'text-text-main',
    };
@endphp

<x-card class="p-1 border border-gray-100 shadow-sm">
    <p class="text-xs text-text-muted uppercase font-semibold">{{ $label }}</p>
    <p class="text-2xl font-bold {{ $valueClass }}">{{ $value }}</p>
</x-card>
