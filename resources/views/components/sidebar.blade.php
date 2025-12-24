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

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-blue-900 bg-blue-900/50">
            <div class="flex flex-col items-center justify-center text-xs text-blue-200 font-medium text-center">
                <span>Tahun Ajaran 2025/2026</span>
                <span class="text-[10px] opacity-75">Semester Ganjil</span>
            </div>
        </div>
    </div>
</aside>
