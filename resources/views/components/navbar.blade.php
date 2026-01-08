@props(['title' => 'Dashboard', 'userPrefix' => 'User'])

@php
    $userName = auth()->check() ? auth()->user()->name : 'Guest';
    $userRole = auth()->check() ? ucfirst(auth()->user()->role) : $userPrefix;
@endphp

<header class="hidden md:flex h-16 bg-bg-surface border-b border-gray-200 items-center justify-between px-6">
    <div>
        <!-- Page Title -->
        <h2 class="text-xl font-semibold text-gray-800">
            {{ $title }}
        </h2>
    </div>

    <!-- Right Actions -->
    <div class="flex items-center gap-4">
        <!-- Notification Bell -->
        <button class="relative p-1 text-text-muted hover:text-primary transition-colors">
            <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </button>

        <!-- User Profile -->
        <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary to-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                {{ strtoupper(substr($userName, 0, 2)) }}
            </div>
            <div class="hidden md:block text-right">
                <p class="text-sm font-semibold text-text-main leading-tight">{{ $userName }}</p>
                <p class="text-xs text-text-muted">{{ $userRole }}</p>
            </div>
        </div>
    </div>
</header>

