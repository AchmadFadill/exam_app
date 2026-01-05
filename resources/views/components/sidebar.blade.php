<aside 
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-30 w-64 bg-primary text-white border-r border-blue-900 shadow-lg md:shadow-none transition-transform duration-300 ease-in-out md:translate-x-0 md:relative md:block"
>
    <div class="h-full flex flex-col relative">
        <!-- Logo -->
        <div class="hidden md:flex flex-col items-center justify-center h-24 border-b border-blue-900 bg-blue-800">
            <div class="flex items-center gap-3">
                <!-- Real Logo -->
                <div class="bg-white p-1 rounded-full shadow-sm w-12 h-12 flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('img/logo_school.jpg') }}" alt="Logo Sekolah" class="w-full h-full object-cover">
                </div>
                <div class="text-left leading-tight">
                    <h1 class="font-bold text-lg tracking-wide">CBT</h1>
                    <p class="text-xs text-blue-200 opacity-80 font-medium">SMAIT Baitul Muslim</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-2">
            {{ $slot }}
        </nav>

        <!-- Sidebar Footer with Logout -->
        <div class="p-4 border-t border-blue-900 bg-blue-900/50 space-y-3">
            <div class="flex flex-col items-center justify-center text-xs text-blue-200 font-medium text-center">
                <span>Tahun Ajaran 2025/2026</span>
                <span class="text-[10px] opacity-75">Semester Ganjil</span>
            </div>
            
            <!-- Logout Button -->
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-red-200 bg-red-600/20 hover:bg-red-600/40 rounded-xl transition-all duration-200 border border-red-500/20 hover:border-red-500/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>
</aside>
