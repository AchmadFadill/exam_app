@props(['title' => 'Dashboard', 'userPrefix' => 'User'])

@php
    $userName = auth()->check() ? auth()->user()->name : 'Guest';
    $userRole = auth()->check() ? ucfirst(auth()->user()->role) : $userPrefix;
@endphp

<header 
    x-data="{ openNotifications: false, openProfile: false }"
    class="hidden md:flex h-16 bg-bg-surface border-b border-border-main items-center justify-between px-6 sticky top-0 z-40 transition-all duration-200"
>
    <div>
        <!-- Page Title -->
        <h2 class="text-xl font-bold text-text-main tracking-tight">
            {{ $title }}
        </h2>
    </div>

    <!-- Right Actions -->
    <div class="flex items-center gap-6">
        <!-- Notification Bell -->
        <div class="relative">
            <button 
                @click="openNotifications = !openNotifications; openProfile = false"
                class="relative p-2 text-text-muted hover:text-primary active:scale-95 transition-all duration-200 rounded-xl hover:bg-primary/10"
            >
                <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-bg-surface"></span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </button>

            <!-- Notification Dropdown -->
            <div 
                x-show="openNotifications"
                @click.away="openNotifications = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="absolute right-0 mt-3 w-80 bg-bg-surface rounded-2xl shadow-2xl border border-border-main py-2 overflow-hidden z-50"
                style="display: none;"
            >
                <div class="px-4 py-2 border-b border-border-subtle bg-bg-app/50">
                    <h3 class="font-bold text-sm text-text-main">Notifikasi</h3>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <a href="#" class="block px-4 py-3 hover:bg-primary/5 transition-colors border-b border-border-subtle">
                        <p class="text-xs font-semibold text-text-main">Ujian Matematika</p>
                        <p class="text-[10px] text-text-muted mt-0.5">Andi Wijaya baru saja menyelesaikan ujian.</p>
                        <span class="text-[9px] text-primary font-medium mt-1 inline-block">2 menit yang lalu</span>
                    </a>
                    <a href="#" class="block px-4 py-3 hover:bg-primary/5 transition-colors border-b border-border-subtle">
                        <p class="text-xs font-semibold text-text-main">Peringatan Keamanan</p>
                        <p class="text-[10px] text-text-muted mt-0.5">Terdeteksi perpindahan tab pada ujian Fisika.</p>
                        <span class="text-[9px] text-primary font-medium mt-1 inline-block">15 menit yang lalu</span>
                    </a>
                </div>
                <div class="px-4 py-2 bg-bg-app/50 text-center">
                    <a href="#" class="text-[10px] font-bold text-primary hover:underline">Lihat Semua Notifikasi</a>
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="relative pl-6 border-l border-border-main">
            <button 
                @click="openProfile = !openProfile; openNotifications = false"
                class="flex items-center gap-3 group focus:outline-none"
            >
                <div class="relative">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-white font-bold text-sm shadow-lg group-hover:shadow-primary/20 transition-all duration-300 transform group-hover:-translate-y-0.5 group-active:translate-y-0">
                        {{ strtoupper(substr($userName, 0, 2)) }}
                    </div>
                </div>
                <div class="hidden md:block text-left">
                    <p class="text-sm font-bold text-text-main leading-tight group-hover:text-primary transition-colors italic">{{ $userName }}</p>
                    <p class="text-[10px] text-text-muted font-medium tracking-wide uppercase">{{ $userRole }}</p>
                </div>
                <svg class="h-4 w-4 text-border-main group-hover:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div 
                x-show="openProfile"
                @click.away="openProfile = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="absolute right-0 mt-3 w-56 bg-bg-surface rounded-2xl shadow-2xl border border-border-main py-2 z-50 overflow-hidden"
                style="display: none;"
            >
                <div class="px-4 py-3 border-b border-border-subtle bg-bg-app/50">
                    <p class="text-xs font-bold text-text-main truncate">{{ $userName }}</p>
                    <p class="text-[10px] text-text-muted truncate">{{ auth()->user()->email ?? '-' }}</p>
                </div>

                <div class="py-1">

                    <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs text-text-muted hover:bg-primary/5 hover:text-primary transition-all duration-200 italic font-medium">
                        <svg class="w-4 h-4 text-text-muted/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        </svg>
                        Pengaturan 
                    </a>
                </div>

                <div class="pt-1 mt-1 border-t border-border-subtle">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 w-full px-4 py-3 text-xs text-red-600 hover:bg-red-50 transition-all duration-200 italic font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Keluar Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

