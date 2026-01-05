<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[var(--color-bg-app)]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Login - CBT Exam</title>
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

                <h1 class="text-3xl font-extrabold text-[var(--color-text-main)] tracking-tight">Portal Admin</h1>
                <p class="mt-2 text-sm text-[var(--color-text-muted)] font-medium">CBT SMAIT Baitul Muslim</p>
            </div>
            
            <form class="mt-10 space-y-6" action="{{ route('login') }}" method="POST">
                @csrf

                {{-- Global Error Message --}}
                @if ($errors->any())
                <div class="p-4 rounded-2xl bg-red-50 border border-red-100">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-semibold text-red-700">{{ $errors->first() }}</p>
                    </div>
                </div>
                @endif

                <div class="space-y-5">
                    <!-- Email Field -->
                    <div class="space-y-2 group">
                        <label for="email" class="block text-xs font-bold text-[var(--color-text-main)] uppercase tracking-[0.15em] ml-1 opacity-60 group-focus-within:opacity-100 transition-opacity">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[var(--color-text-muted)] transition-colors group-focus-within:text-[var(--color-primary)]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                                </svg>
                            </span>
                            <input id="email" name="email" type="email" required value="{{ old('email') }}"
                                class="block w-full pl-12 pr-4 py-4 bg-gray-50/50 border border-gray-200 text-sm rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-[var(--color-primary)] focus:bg-white outline-none transition-all placeholder:text-gray-400 font-medium @error('email') border-red-300 @enderror" 
                                placeholder="nama@sekolah.sch.id">
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
                    <input id="remember" name="remember" type="checkbox" class="h-5 w-5 text-[var(--color-primary)] focus:ring-[var(--color-primary)]/10 border-gray-300 rounded-lg cursor-pointer transition-all">
                    <label for="remember" class="ml-3 block text-sm text-[var(--color-text-muted)] font-semibold cursor-pointer select-none">
                        Ingat Saya
                    </label>
                </div>

                <button type="submit" class="group relative w-full flex justify-center py-4.5 px-6 border border-transparent text-sm font-bold rounded-2xl text-white bg-[var(--color-primary)] hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/20 transition-all shadow-xl shadow-blue-900/20 active:scale-[0.98]">
                    <span class="relative z-10">MASUK DASHBOARD</span>
                </button>
            </form>

            <!-- Alternative Login Links -->
            <div class="mt-8 pt-6 border-t border-gray-100">
                <p class="text-xs text-center text-[var(--color-text-muted)] font-semibold mb-4 uppercase tracking-wider">Login Sebagai</p>
                <div class="flex gap-3">
                    <a href="{{ route('teacher.login') }}" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition-all border border-emerald-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Guru
                    </a>
                    <a href="{{ route('student.login') }}" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl transition-all border border-blue-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Siswa
                    </a>
                </div>
            </div>
            
            <!-- Bottom Branding -->
            <div class="mt-8 text-center border-t border-gray-100 pt-8">
                <p class="text-[10px] text-slate-400 font-bold tracking-[0.25em] uppercase opacity-70">
                    &copy; 2025 CBT System &bull; SMAIT Baitul Muslim
                </p>
            </div>
        </div>
    </div>
</body>
</html>
