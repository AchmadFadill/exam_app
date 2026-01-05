<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[var(--color-bg-app)]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Siswa - CBT Exam</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 antialiased selection:bg-blue-100 selection:text-blue-900">
    <div class="max-w-md w-full relative">
        <!-- Subtle branding accent -->
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-[var(--color-primary)]/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-[var(--color-secondary)]/5 rounded-full blur-3xl"></div>

        <div class="relative bg-[var(--color-bg-surface)] p-8 sm:p-12 rounded-[2rem] shadow-[0_20px_60px_-15px_rgba(30,64,175,0.08)] border border-white/60">
            <div class="text-center">
                <!-- Circular School Logo -->
                <div class="relative inline-block mb-10 group">
                    <div class="absolute inset-0 bg-[var(--color-primary)]/10 blur-xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative w-24 h-24 bg-white rounded-full flex items-center justify-center p-3 shadow-xl border border-gray-50 transition-transform duration-500 group-hover:scale-105">
                        <img src="{{ asset('img/logo_school.jpg') }}" alt="Logo Sekolah" class="w-full h-full object-contain">
                    </div>
                </div>

                <h1 class="text-3xl font-extrabold text-[var(--color-text-main)] tracking-tight">Portal Siswa</h1>
                <p class="mt-2 text-sm text-[var(--color-text-muted)] font-medium">CBT SMAIT Baitul Muslim</p>
            </div>
            
            <form class="mt-10 space-y-6" action="{{ route('student.dashboard') }}" method="GET">
                @csrf
                <div class="space-y-5">
                    <!-- NISN Field -->
                    <div class="space-y-2 group">
                        <label for="nis" class="block text-xs font-bold text-[var(--color-text-main)] uppercase tracking-[0.15em] ml-1 opacity-60 group-focus-within:opacity-100 transition-opacity">NIS / NISN</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[var(--color-text-muted)] transition-colors group-focus-within:text-[var(--color-primary)]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                </svg>
                            </span>
                            <input id="nis" name="nis" type="text" required 
                                class="block w-full pl-12 pr-4 py-4 bg-gray-50/50 border border-gray-200 text-sm rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-[var(--color-primary)] focus:bg-white outline-none transition-all placeholder:text-gray-400 font-medium" 
                                placeholder="Masukkan NIS / NISN">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2 group">
                        <div class="flex items-center justify-between ml-1">
                            <label for="password" class="block text-xs font-bold text-[var(--color-text-main)] uppercase tracking-[0.15em] opacity-60 group-focus-within:opacity-100 transition-opacity">Password</label>
                            <a href="#" class="text-xs font-bold text-[var(--color-primary)] hover:underline transition-colors">Lupa password?</a>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[var(--color-text-muted)] transition-colors group-focus-within:text-[var(--color-primary)]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input id="password" name="password" type="password" required 
                                class="block w-full pl-12 pr-4 py-4 bg-gray-50/50 border border-gray-200 text-sm rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-[var(--color-primary)] focus:bg-white outline-none transition-all placeholder:text-gray-400 font-medium" 
                                placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <div class="flex items-center px-1">
                    <input id="remember-me" name="remember-me" type="checkbox" class="h-5 w-5 text-[var(--color-primary)] focus:ring-[var(--color-primary)]/10 border-gray-300 rounded-lg cursor-pointer transition-all">
                    <label for="remember-me" class="ml-3 block text-sm text-[var(--color-text-muted)] font-semibold cursor-pointer select-none">
                        Ingat Saya
                    </label>
                </div>

                <button type="submit" class="group relative w-full flex justify-center py-4.5 px-6 border border-transparent text-sm font-bold rounded-2xl text-white bg-[var(--color-primary)] hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/20 transition-all shadow-xl shadow-blue-900/20 active:scale-[0.98]">
                    <span class="relative z-10">MASUK DASHBOARD</span>
                </button>
            </form>

            <!-- Bottom Branding -->
            <div class="mt-12 text-center border-t border-gray-100 pt-8">
                <p class="text-[10px] text-slate-400 font-bold tracking-[0.25em] uppercase opacity-70">
                    &copy; 2025 CBT System &bull; SMAIT Baitul Muslim
                </p>
            </div>
        </div>
    </div>
</body>
</html>
