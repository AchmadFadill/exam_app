<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - SMAIT Baitul Muslim</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-bg-app font-sans antialiased text-text-main">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Sidebar -->
        <aside class="w-full md:w-64 bg-primary text-white flex-shrink-0">
            <div class="p-4 flex items-center justify-center border-b border-blue-900">
                <div class="font-bold text-xl tracking-wide text-center">
                    <span class="text-secondary">SMAIT</span> CBT
                </div>
            </div>
            <nav class="mt-4 px-2 space-y-1">
                <a href="/admin/dashboard" class="flex items-center px-4 py-3 bg-blue-900 rounded-lg text-white group transition-colors">
                    <svg class="h-5 w-5 mr-3 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Dashboard
                </a>

                <!-- Kelola Pengguna Group -->
                <div class="px-4 py-2 text-xs uppercase text-blue-300 font-semibold mt-4">Kelola Pengguna</div>
                <a href="#" class="flex items-center px-4 py-2 text-blue-100 hover:bg-blue-700 hover:text-white rounded-lg transition-colors">
                    <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Data Guru
                </a>
                <a href="#" class="flex items-center px-4 py-2 text-blue-100 hover:bg-blue-700 hover:text-white rounded-lg transition-colors">
                    <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Data Siswa
                </a>
                <a href="#" class="flex items-center px-4 py-2 text-blue-100 hover:bg-blue-700 hover:text-white rounded-lg transition-colors">
                    <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Data Kelas
                </a>

                <!-- Akademik -->
                <div class="px-4 py-2 text-xs uppercase text-blue-300 font-semibold mt-4">Akademik</div>
                <a href="#" class="flex items-center px-4 py-2 text-blue-100 hover:bg-blue-700 hover:text-white rounded-lg transition-colors">
                    <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Mata Pelajaran
                </a>
                <!-- Monitoring -->
                 <a href="#" class="flex items-center px-4 py-2 text-blue-100 hover:bg-blue-700 hover:text-white rounded-lg transition-colors">
                    <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Monitoring Ujian 
                    <span class="ml-auto bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full animate-pulse">LIVE</span>
                </a>

                <!-- Laporan -->
                <div class="px-4 py-2 text-xs uppercase text-blue-300 font-semibold mt-4">Laporan</div>
                <a href="#" class="flex items-center px-4 py-2 text-blue-100 hover:bg-blue-700 hover:text-white rounded-lg transition-colors">
                     <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Hasil Ujian
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="bg-bg-surface shadow-sm z-10 p-4 flex justify-between items-center">
                <div class="flex items-center md:hidden">
                    <button class="text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span class="ml-2 font-semibold text-gray-700">SMAIT CBT</span>
                </div>
                
                <h2 class="hidden md:block text-xl font-semibold text-gray-800">
                    @yield('title', 'Dashboard')
                </h2>

                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div class="flex items-center">
                        <img class="h-8 w-8 rounded-full border border-gray-200" src="https://ui-avatars.com/api/?name=Admin+Sekolah&background=random" alt="Admin">
                        <span class="ml-2 text-sm font-medium text-gray-700 hidden md:block">Administrator</span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-bg-app p-6">
                 @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
