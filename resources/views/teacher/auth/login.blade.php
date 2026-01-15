<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[var(--color-bg-app)]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Guru - CBT Exam</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 antialiased selection:bg-emerald-100 selection:text-emerald-900">
    <div class="max-w-md w-full relative">
        <!-- Subtle branding accent -->
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-teal-500/5 rounded-full blur-3xl"></div>

        <div class="relative bg-[var(--color-bg-surface)] p-8 sm:p-10 rounded-[2rem] shadow-[0_20px_60px_-15px_rgba(16,185,129,0.08)] border border-white/60">
            <div class="text-center">
                <!-- Circular School Logo -->
                <div class="relative inline-block mb-6 group">
                    <div class="absolute inset-0 bg-emerald-500/10 blur-xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative w-20 h-20 bg-white rounded-full flex items-center justify-center p-2 shadow-xl border border-gray-50 transition-transform duration-500 group-hover:scale-105">
                        <img src="{{ asset('img/logo_school.jpg') }}" alt="Logo Sekolah" class="w-full h-full object-contain">
                    </div>
                </div>

                <h1 class="text-2xl font-extrabold text-[var(--color-text-main)] tracking-tight">Portal Guru</h1>
                <p class="mt-1 text-xs text-[var(--color-text-muted)] font-medium">CBT SMAIT Baitul Muslim</p>
            </div>
            
            <form class="mt-6 space-y-4" action="{{ route('teacher.login') }}" method="POST">
                @csrf

                {{-- Global Error Message --}}
                @if ($errors->any())
                <div class="p-3 rounded-2xl bg-red-50 border border-red-100 mb-3">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-[13px] font-semibold text-red-700">{{ $errors->first() }}</p>
                    </div>
                </div>
                @endif

                <div class="space-y-4">
                    <!-- Email Field -->
                    <div class="space-y-1.5 group">
                        <label for="email" class="block text-[10px] font-bold text-[var(--color-text-main)] uppercase tracking-[0.15em] ml-1 opacity-60 group-focus-within:opacity-100 transition-opacity">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[var(--color-text-muted)] transition-colors group-focus-within:text-emerald-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                                </svg>
                            </span>
                            <input id="email" name="email" type="email" required value="{{ old('email') }}"
                                class="block w-full pl-12 pr-4 py-3.5 bg-gray-50/50 border border-gray-200 text-sm rounded-2xl focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 focus:bg-white outline-none transition-all placeholder:text-gray-400 font-medium @error('email') border-red-300 @enderror" 
                                placeholder="nama@sekolah.sch.id">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-1.5 group">
                        <div class="flex items-center justify-between ml-1">
                            <label for="password" class="block text-[10px] font-bold text-[var(--color-text-main)] uppercase tracking-[0.15em] opacity-60 group-focus-within:opacity-100 transition-opacity">Password</label>
                            <a href="#" class="text-[10px] font-bold text-emerald-600 hover:underline transition-colors">Lupa password?</a>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[var(--color-text-muted)] transition-colors group-focus-within:text-emerald-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input id="password" name="password" type="password" required 
                                class="block w-full pl-12 pr-12 py-3.5 bg-gray-50/50 border border-gray-200 text-sm rounded-2xl focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 focus:bg-white outline-none transition-all placeholder:text-gray-400 font-medium" 
                                placeholder="••••••••">
                            <button type="button" onclick="togglePassword('password', 'password-icon')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-emerald-600 transition-colors">
                                <svg id="password-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path class="eye-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path class="eye-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    <path class="eye-closed hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057 5.064-7 9.542-7 1.222 0 2.391.22 3.474.625M19.74 19.74A12.401 12.401 0 0112 21c-4.478 0-8.268-2.943-9.542-7 1.274-4.057 5.064-7 9.542-7 .92 0 1.817.11 2.68.315M15 12a3 3 0 11-6 0 3 3 0 016 0zM3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center px-1">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-emerald-600 focus:ring-emerald-500/10 border-gray-300 rounded-lg cursor-pointer transition-all">
                    <label for="remember" class="ml-2.5 block text-xs text-[var(--color-text-muted)] font-semibold cursor-pointer select-none">
                        Ingat Saya
                    </label>
                </div>

                <button type="submit" class="group relative w-full flex justify-center py-3.5 px-6 border border-transparent text-sm font-bold rounded-2xl text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/20 transition-all shadow-xl shadow-emerald-900/20 active:scale-[0.98]">
                    <span class="relative z-10">MASUK DASHBOARD</span>
                </button>
            </form>

            <!-- Alternative Login Links -->
            <div class="mt-5 pt-4 border-t border-gray-100">
                <p class="text-[10px] text-center text-[var(--color-text-muted)] font-bold mb-2.5 uppercase tracking-wider">Login Sebagai</p>
                <div class="flex gap-2">
                    <a href="{{ route('student.login') }}" class="flex-1 flex items-center justify-center gap-1.5 px-2 py-2 text-[11px] font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl transition-all border border-blue-100">
                        Siswa
                    </a>
                    <a href="{{ route('login') }}" class="flex-1 flex items-center justify-center gap-1.5 px-2 py-2 text-[11px] font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-xl transition-all border border-gray-100">
                        Admin
                    </a>
                </div>
            </div>
            
            <!-- Bottom Branding -->
            <div class="mt-6 text-center border-t border-gray-100 pt-5">
                <p class="text-[9px] text-slate-400 font-bold tracking-[0.25em] uppercase opacity-70">
                    &copy; 2025 CBT System &bull; SMAIT Baitul Muslim
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const openPaths = icon.querySelectorAll('.eye-open');
            const closedPath = icon.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                openPaths.forEach(p => p.classList.add('hidden'));
                closedPath.classList.remove('hidden');
            } else {
                input.type = 'password';
                openPaths.forEach(p => p.classList.remove('hidden'));
                closedPath.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
