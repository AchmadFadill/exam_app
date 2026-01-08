<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CBT SMAIT Baitul Muslim</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full antialiased selection:bg-blue-500 selection:text-white overflow-x-hidden">
    
    <!-- Background Effects -->
    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-[url('/public/img/grid.svg')] bg-center [mask-image:linear-gradient(180deg,white,rgba(255,255,255,0))]"></div>
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-500/20 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-emerald-500/20 rounded-full blur-[100px] animate-pulse" style="animation-delay: 2s"></div>
    </div>

    <div class="relative z-10 min-h-full flex flex-col items-center justify-center p-4 sm:p-8">
        
        <!-- Header -->
        <div class="text-center mb-12 sm:mb-16 space-y-4">
            <div class="inline-flex items-center justify-center p-2 bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl mb-6 shadow-2xl">
                <img src="{{ asset('img/logo_school.jpg') }}" alt="Logo" class="w-16 h-16 object-contain rounded-xl bg-white p-1">
            </div>
            <h1 class="text-4xl sm:text-6xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white via-blue-100 to-blue-200 tracking-tight pb-2">
                CBT System
            </h1>
            <p class="text-lg sm:text-xl text-slate-400 font-medium max-w-lg mx-auto leading-relaxed">
                Platform Ujian Berbasis Komputer<br>
                <span class="text-slate-300">SMAIT Baitul Muslim</span>
            </p>
        </div>

        <!-- Role Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-5xl px-4">
            
            <!-- Siswa Card -->
            <a href="{{ route('student.login') }}" class="group relative p-8 bg-slate-800/50 hover:bg-slate-800 rounded-[2rem] border border-slate-700 hover:border-blue-500/50 shadow-xl hover:shadow-blue-900/20 backdrop-blur-xl hover:-translate-y-2 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/0 via-blue-500/0 to-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-[2rem]"></div>
                
                <div class="relative flex flex-col items-center text-center h-full">
                    <div class="w-16 h-16 bg-slate-700/50 rounded-2xl flex items-center justify-center mb-6 border border-slate-600 group-hover:border-blue-500/30 group-hover:bg-blue-500/20 transition-all">
                        <svg class="w-8 h-8 text-slate-300 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2 group-hover:text-blue-400 transition-colors">Siswa</h3>
                    <p class="text-slate-400 text-sm leading-relaxed mb-8 group-hover:text-slate-300 transition-colors">
                        Masuk untuk mengerjakan ujian dan melihat hasil nilai.
                    </p>
                    <div class="mt-auto px-6 py-3 bg-slate-700 rounded-xl text-slate-300 font-semibold text-sm group-hover:bg-blue-600 group-hover:text-white transition-all w-full flex items-center justify-center gap-2">
                        Login Siswa
                    </div>
                </div>
            </a>

            <!-- Guru Card -->
            <a href="{{ route('teacher.login') }}" class="group relative p-8 bg-slate-800/50 hover:bg-slate-800 rounded-[2rem] border border-slate-700 hover:border-emerald-500/50 shadow-xl hover:shadow-emerald-900/20 backdrop-blur-xl hover:-translate-y-2 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/0 via-emerald-500/0 to-emerald-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-[2rem]"></div>
                
                <div class="relative flex flex-col items-center text-center h-full">
                    <div class="w-16 h-16 bg-slate-700/50 rounded-2xl flex items-center justify-center mb-6 border border-slate-600 group-hover:border-emerald-500/30 group-hover:bg-emerald-500/20 transition-all">
                        <svg class="w-8 h-8 text-slate-300 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">Guru</h3>
                    <p class="text-slate-400 text-sm leading-relaxed mb-8 group-hover:text-slate-300 transition-colors">
                        Kelola bank soal, buat jadwal ujian, dan rekap nilai siswa.
                    </p>
                    <div class="mt-auto px-6 py-3 bg-slate-700 rounded-xl text-slate-300 font-semibold text-sm group-hover:bg-emerald-500 group-hover:text-white transition-all w-full">
                        Login Guru
                    </div>
                </div>
            </a>

            <!-- Admin Card -->
            <a href="{{ route('login') }}" class="group relative p-8 bg-slate-800/50 hover:bg-slate-800 rounded-[2rem] border border-slate-700 hover:border-indigo-500/50 shadow-xl hover:shadow-indigo-900/20 backdrop-blur-xl hover:-translate-y-2 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/0 via-indigo-500/0 to-indigo-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-[2rem]"></div>
                
                <div class="relative flex flex-col items-center text-center h-full">
                    <div class="w-16 h-16 bg-slate-700/50 rounded-2xl flex items-center justify-center mb-6 border border-slate-600 group-hover:border-indigo-500/30 group-hover:bg-indigo-500/20 transition-all">
                        <svg class="w-8 h-8 text-slate-300 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2 group-hover:text-indigo-400 transition-colors">Admin</h3>
                    <p class="text-slate-400 text-sm leading-relaxed mb-8 group-hover:text-slate-300 transition-colors">
                        Pengaturan sistem, manajemen user, dan master data.
                    </p>
                    <div class="mt-auto px-6 py-3 bg-slate-700 rounded-xl text-slate-300 font-semibold text-sm group-hover:bg-indigo-500 group-hover:text-white transition-all w-full">
                        Login Admin
                    </div>
                </div>
            </a>

        </div>

        <!-- Footer -->
        <div class="mt-16 text-center">
            <p class="text-slate-500 text-sm font-medium">
                &copy; {{ date('Y') }} CBT System SMAIT Baitul Muslim. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
