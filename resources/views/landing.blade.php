<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $app_name ?? 'CBT System' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}?v=3">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="antialiased bg-white text-slate-900 overflow-x-hidden">
    
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    @if(isset($app_logo) && $app_logo)
                        <img src="{{ asset('storage/' . $app_logo) }}" alt="Logo" class="w-10 h-10 rounded-full shadow-sm border border-slate-100 p-0.5 object-cover">
                    @else
                        <img src="{{ asset('img/logo_school.jpg') }}" alt="Logo" class="w-10 h-10 rounded-full shadow-sm border border-slate-100 p-0.5">
                    @endif
                    <span class="text-xl font-bold text-slate-900 tracking-tight">CBT System</span>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <!-- Login Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary rounded-xl text-white text-sm font-semibold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                            Masuk 
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        
                        <div x-show="open" 
                             @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                             class="absolute right-0 mt-3 w-56 origin-top-right bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50 overflow-hidden">
                            <a href="{{ route('student.login') }}" class="group flex items-center gap-3 px-4 py-3 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors text-primary">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </div>
                                <span class="font-semibold">Login Siswa</span>
                            </a>
                            <a href="{{ route('teacher.login') }}" class="group flex items-center gap-3 px-4 py-3 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-secondary/10 flex items-center justify-center group-hover:bg-secondary group-hover:text-white transition-colors text-secondary">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                                <span class="font-semibold">Login Guru</span>
                            </a>
                            <div class="h-px bg-slate-100 my-1 mx-2"></div>
                            <a href="{{ route('login') }}" class="group flex items-center gap-3 px-4 py-3 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center group-hover:bg-slate-700 group-hover:text-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                </div>
                                <span class="font-semibold text-slate-800">Login Admin</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-24 pb-20 lg:pt-36 lg:pb-32 bg-white overflow-hidden">
        <!-- Background Decoration -->
        <div class="absolute inset-0 z-0">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-blue-400/20 blur-[120px] rounded-full"></div>
            <div class="absolute top-[20%] -right-[5%] w-[30%] h-[30%] bg-secondary/10 blur-[100px] rounded-full"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-8 text-center lg:text-left">
                    <h1 class="text-5xl lg:text-7xl font-extrabold text-slate-900 leading-tight">
                        CBT <br>
                        <span class="text-primary">{{ $app_name ?? 'Digital Learning' }}</span>
                    </h1>
                    <p class="text-lg text-slate-600 max-w-xl mx-auto lg:mx-0 leading-relaxed font-medium">
                        Wujudkan evaluasi pembelajaran yang efisien, transparan, dan terintegrasi dengan sistem Computer Based Test tercanggih.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-4">
                        <a href="#mulai" class="px-8 py-4 bg-secondary rounded-2xl text-slate-900 font-bold text-lg hover:bg-amber-400 transition-all shadow-xl shadow-secondary/20 hover:-translate-y-1">
                            Mulai Sekarang
                        </a>
                    </div>
                </div>
                <div class="relative lg:ml-12">
                    <div class="absolute inset-0 bg-primary/5 blur-[120px] rounded-full transform rotate-12"></div>
                    <img src="{{ asset('img/hero-ilustration.png') }}" alt="CBT Illustration" class="relative z-10 w-full max-w-2xl mx-auto animate-float">
                </div>
            </div>
        </div>

        <!-- Wave Separator -->
        <div class="absolute -bottom-[1px] left-0 w-full overflow-hidden leading-[0] z-20">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="relative block w-full h-[101px] fill-primary transform translate-y-[1px]">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C57.86,40.35,163.31,85.43,321.39,56.44Z"></path>
            </svg>
        </div>
    </section>

    <!-- Main Content (Role Selection) -->
    <main id="mulai" class="py-24 bg-primary relative overflow-hidden">
        <!-- Background Decoration -->
        <div class="absolute inset-0 z-0">
            <div class="absolute top-0 right-0 w-1/2 h-1/2 bg-primary/20 blur-[120px] rounded-full"></div>
            <div class="absolute bottom-0 left-0 w-1/2 h-1/2 bg-secondary/10 blur-[120px] rounded-full"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16 space-y-4">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white">Selamat Datang</h2>
                <p class="text-blue-100 font-medium">Pilih akses masuk sesuai dengan peran Anda di sekolah</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Siswa Card -->
                <a href="{{ route('student.login') }}" class="group relative p-8 bg-white/10 backdrop-blur-md rounded-3xl border border-white/10 hover:border-white/30 transition-all duration-300 shadow-xl hover:-translate-y-2">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Siswa</h3>
                    <p class="text-blue-100 text-sm mb-6 leading-relaxed">Akses portal ujian untuk mengerjakan soal dan melihat hasil ujian Anda.</p>
                    <div class="flex items-center text-secondary font-bold gap-2 group-hover:gap-3 transition-all">
                        Login Siswa
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </div>
                </a>

                <!-- Guru Card -->
                <a href="{{ route('teacher.login') }}" class="group relative p-8 bg-white/10 backdrop-blur-md rounded-3xl border border-white/10 hover:border-white/30 transition-all duration-300 shadow-xl hover:-translate-y-2">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Guru</h3>
                    <p class="text-blue-100 text-sm mb-6 leading-relaxed">Kelola bank soal, jadwal ujian, dan pantau hasil belajar siswa.</p>
                    <div class="flex items-center text-secondary font-bold gap-2 group-hover:gap-3 transition-all">
                        Login Guru
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </div>
                </a>

                <!-- Admin Card -->
                <a href="{{ route('login') }}" class="group relative p-8 bg-white/10 backdrop-blur-md rounded-3xl border border-white/10 hover:border-white/30 transition-all duration-300 shadow-xl hover:-translate-y-2">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Admin</h3>
                    <p class="text-blue-100 text-sm mb-6 leading-relaxed">Pengaturan sistem utama, manajemen pengguna, dan data master sekolah.</p>
                    <div class="flex items-center text-secondary font-bold gap-2 group-hover:gap-3 transition-all">
                        Login Admin
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-12 border-t border-white/10 bg-primary text-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-blue-200/60 text-sm font-medium">
                &copy; {{ date('Y') }} CBT {{ $app_name ?? 'System' }}. All rights reserved.
            </p>
        </div>
    </footer>

    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</body>
</html>
