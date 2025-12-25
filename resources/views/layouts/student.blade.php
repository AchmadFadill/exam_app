<x-app-layout title="Student">
    <!-- Sidebar -->
    <x-slot name="sidebar">
        <x-sidebar>
            <div class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wider mb-2">Menu Utama</div>
            
            <x-sidebar-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </x-slot>
                Dashboard
            </x-sidebar-link>

            <div class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wider mt-6 mb-2">Ujian</div>
            
             <x-sidebar-link :href="route('student.results')" :active="request()->routeIs('student.results*')">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </x-slot>
                Hasil Ujian
            </x-sidebar-link>

        </x-sidebar>
    </x-slot>

    <!-- Top Navbar -->
    <x-slot name="navbar">
        <x-navbar :title="View::yieldContent('title', 'Dashboard')" userPrefix="Siswa" />
    </x-slot>

    <!-- Content -->
    @yield('content')
</x-app-layout>
