<x-app-layout title="Admin">
    <!-- Sidebar -->
    <x-slot name="sidebar">
        <x-sidebar>
             <x-sidebar-link href="/admin/dashboard" :active="request()->is('admin/dashboard')">
                 <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                 </x-slot>
                 Dashboard
            </x-sidebar-link>

            <!-- Kelola Pengguna Group -->
            <div class="px-4 py-2 text-xs uppercase text-blue-300 font-semibold mt-4">Kelola Pengguna</div>
            <x-sidebar-link href="{{ route('admin.teachers') }}" :active="request()->routeIs('admin.teachers')">
                 <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                 </x-slot>
                 Data Guru
            </x-sidebar-link>
            
            <x-sidebar-link href="{{ route('admin.students') }}" :active="request()->routeIs('admin.students')">
                 <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                 </x-slot>
                 Data Siswa
            </x-sidebar-link>
            
            <x-sidebar-link href="{{ route('admin.classes') }}" :active="request()->routeIs('admin.classes')">
                 <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                 </x-slot>
                 Data Kelas
            </x-sidebar-link>

            <!-- Akademik -->
            <div class="px-4 py-2 text-xs uppercase text-blue-300 font-semibold mt-4">Akademik</div>
            <x-sidebar-link href="{{ route('admin.subjects') }}" :active="request()->routeIs('admin.subjects')">
                 <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                 </x-slot>
                 Mata Pelajaran
            </x-sidebar-link>

            <div class="px-4 py-2 text-xs uppercase text-blue-300 font-semibold mt-4">Ujian</div>


            <!-- Monitoring -->
            <x-sidebar-link href="{{ route('admin.monitor') }}" :active="request()->routeIs('admin.monitor')">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </x-slot>
                Monitoring Ujian
            </x-sidebar-link>
            
            <!-- Laporan -->
            <div class="px-4 py-2 text-xs uppercase text-blue-300 font-semibold mt-4">Laporan</div>
             <x-sidebar-link href="#">
                 <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                 </x-slot>
                 Hasil Ujian
            </x-sidebar-link>
        </x-sidebar>
    </x-slot>

    <!-- Top Navbar -->
    <x-slot name="navbar">
        <x-navbar :title="View::yieldContent('title', 'Dashboard')" userPrefix="Administrator" />
    </x-slot>

    <!-- Content -->
    @yield('content')
</x-app-layout>
