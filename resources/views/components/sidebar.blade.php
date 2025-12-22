<aside 
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-30 w-64 bg-primary text-white border-r border-blue-900 shadow-lg md:shadow-none transition-transform duration-300 ease-in-out md:translate-x-0 md:relative md:block"
>
    <div class="h-full flex flex-col">
        <!-- Logo -->
        <div class="hidden md:flex items-center justify-center h-16 border-b border-blue-900">
            <div class="font-bold text-xl tracking-wide text-center">
                <span class="text-secondary">SMAIT</span> CBT
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-2">
            {{ $slot }}
        </nav>
    </div>
</aside>
