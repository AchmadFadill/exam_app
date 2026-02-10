@props(['title' => 'Beranda', 'userPrefix' => 'User'])

@php
    $userName = auth()->check() ? auth()->user()->name : 'Tamu';
    $roleKey = auth()->check() ? auth()->user()->role : null;
    $authUser = auth()->user();

    if ($roleKey === 'teacher') {
        $teacherName = $authUser?->name ?: '-';
        $waliClassName = $authUser?->teacher?->classroom?->name;
        $waliText = $waliClassName ? ('Wali Kelas ' . $waliClassName) : 'Bukan Wali Kelas';
        $userRole = $teacherName . ' - ' . $waliText;
    } elseif ($roleKey === 'student') {
        $classText = $authUser?->student?->classroom?->name ?: '-';
        $userRole = 'STUDENT - ' . $classText;
    } elseif ($roleKey === 'admin') {
        $userRole = 'ADMIN';
    } else {
        $userRole = $userPrefix;
    }

    $displayTitle = View::hasSection('title') ? View::yieldContent('title') : $title;

    // Determine Settings Route
    $settingsRoute = match($roleKey) {
        'admin' => route('admin.settings'),
        'teacher' => route('teacher.settings'),
        'student' => route('student.settings'),
        default => '#'
    };
@endphp

<header 
    x-data="{ openNotifications: false, openProfile: false }"
    class="hidden md:flex h-16 bg-bg-surface border-b border-border-main items-center justify-between px-6 sticky top-0 z-40 transition-all duration-200"
>
    <div>
        <!-- Page Title -->
        <h2 class="text-xl font-bold text-text-main tracking-tight">
            <x-title-resolver :title="$displayTitle" />
        </h2>
    </div>

    <!-- Right Actions -->
    <div class="flex items-center gap-6">
        <!-- Notification Bell -->
        <livewire:common.navbar-notifications />

        <!-- User Profile Dropdown -->
        <div class="relative pl-6 border-l border-border-main">
            <button 
                @click="openProfile = !openProfile; openNotifications = false"
                class="flex items-center gap-3 group focus:outline-none"
            >
                <div class="relative">
                    <div class="w-10 h-10 rounded-2xl bg-gray-100 overflow-hidden shadow-sm group-hover:shadow-primary/20 transition-all duration-300 transform group-hover:-translate-y-0.5 group-active:translate-y-0 border border-gray-100">
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="Profile" class="w-full h-full object-cover">
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

                    <a href="{{ $settingsRoute }}" class="flex items-center gap-3 px-4 py-2.5 text-xs text-text-muted hover:bg-primary/5 hover:text-primary transition-all duration-200 italic font-medium">
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
