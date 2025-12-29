<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - {{ $title ?? 'Dashboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-bg-app text-text-main">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col md:flex-row">
        
        <!-- Mobile Header -->
        <div class="md:hidden bg-bg-surface border-b border-gray-200 p-4 flex justify-between items-center sticky top-0 z-20">
            <div class="flex items-center gap-2">
                <div class="font-bold text-xl tracking-wide text-primary">
                    <span class="text-secondary">SMAIT</span> CBT
                </div>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="text-text-muted hover:text-primary focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    <path x-show="sidebarOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Sidebar Slot -->
        {{ $sidebar }}

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-h-screen overflow-hidden bg-bg-app">
            <!-- Navbar Slot -->
            {{ $navbar ?? '' }}

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8">
                {{ $slot }}
            </div>
        </main>
        
        <!-- Overlay for mobile sidebar -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="md:hidden fixed inset-0 z-20 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
    </div>
    
    @livewireScripts

    <!-- Notification System -->
    <div x-data="{ 
        notifications: [],
        add(message) {
            this.notifications.push({ id: Date.now(), message });
            setTimeout(() => {
                this.notifications = this.notifications.filter(n => n.id !== this.notifications[0].id);
            }, 3000);
        }
    }" 
    @notify.window="add($event.detail[0].message)"
    class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-3">
        <template x-for="n in notifications" :key="n.id">
            <div x-transition:enter="transition ease-out duration-300 transform translate-y-4 opacity-0"
                 x-transition:enter-start="translate-y-4 opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transition ease-in duration-200 transform translate-y-2 opacity-0"
                 class="bg-slate-900/90 backdrop-blur-md text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 border border-white/10 min-w-80">
                <div class="bg-green-500/20 p-2 rounded-xl">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold" x-text="n.message"></p>
                </div>
            </div>
        </template>
    </div>
</body>
</html>
