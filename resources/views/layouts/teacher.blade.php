<x-app-layout title="Teacher">
    <!-- Sidebar -->
    <x-slot name="sidebar">
        <x-sidebar>
            <div class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wider mb-2">Menu Utama</div>
            
            <x-sidebar-link :href="route('teacher.dashboard')" :active="request()->routeIs('teacher.dashboard')">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </x-slot>
                Dashboard
            </x-sidebar-link>

            <x-sidebar-link :href="route('teacher.question-bank.index')" :active="request()->routeIs('teacher.question-bank.*')">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </x-slot>
                Bank Soal
            </x-sidebar-link>

            <x-sidebar-link :href="route('teacher.exams.index')" :active="request()->routeIs('teacher.exams.*')">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </x-slot>
                Kelola Ujian
            </x-sidebar-link>
            
            <div class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wider mt-6 mb-2">Pelaksanaan</div>

            <x-sidebar-link :href="route('teacher.monitoring')" :active="request()->routeIs('teacher.monitoring*')">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </x-slot>
                Monitoring Ujian 
            </x-sidebar-link>

            <x-sidebar-link :href="route('teacher.grading.index')" :active="request()->routeIs('teacher.grading.*')">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </x-slot>
                Penilaian  
                <span class="ml-auto bg-secondary text-primary text-xs font-bold px-2 py-0.5 rounded-full">2</span>
            </x-sidebar-link>

            <x-sidebar-link :href="route('teacher.reports.index')" :active="request()->routeIs('teacher.reports.*')">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </x-slot>
                Hasil Ujian
            </x-sidebar-link>
        </x-sidebar>
    </x-slot>

    <!-- Top Navbar -->
    <x-slot name="navbar">
        <x-navbar :title="View::yieldContent('title', 'Dashboard')" userPrefix="Ibu Guru" />
    </x-slot>

    <!-- Content -->
    @yield('content')
</x-app-layout>
