<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[var(--color-bg-app)]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Login - CBT Exam</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 font-sans antialiased">
    <div class="max-w-md w-full">
        <div class="bg-white p-10 rounded-3xl shadow-2xl shadow-blue-900/5 border border-gray-100 relative overflow-hidden">
            <!-- Decorative background element -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-[var(--color-primary)] opacity-5 rounded-bl-full -mr-10 -mt-10"></div>
            
            <div class="relative">
                <div class="text-center">
                    <!-- School Logo (Matching Student Login Style) -->
                    <div class="mx-auto h-24 w-24 bg-white rounded-full flex items-center justify-center shadow-xl border border-gray-50 mb-8 overflow-hidden p-2 transition-transform hover:scale-105 duration-300">
                        <img src="{{ asset('img/logo_school.jpg') }}" alt="Logo Sekolah" class="w-full h-full object-contain">
                    </div>
                    <h2 class="text-3xl font-extrabold text-[var(--color-text-main)] tracking-tight">
                        Portal Staff
                    </h2>
                    <p class="mt-2 text-sm text-[var(--color-text-muted)] font-medium">
                        Admin & Guru SMAIT Baitul Muslim
                    </p>
                </div>
                
                <form class="mt-10 space-y-6" action="#" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="relative group">
                            <label for="email" class="block text-xs font-bold text-[var(--color-text-main)] uppercase tracking-wider mb-1.5 ml-1">Email Adddress</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[var(--color-text-muted)] group-focus-within:text-[var(--color-primary)] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                                    </svg>
                                </div>
                                <input id="email" name="email" type="email" required 
                                    class="block w-full pl-12 pr-4 py-4 bg-gray-50/50 border border-gray-200 text-sm rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-[var(--color-primary)] focus:bg-white outline-none transition-all placeholder:text-gray-400" 
                                    placeholder="nama@sekolah.sch.id">
                            </div>
                        </div>

                        <div class="relative group">
                            <div class="flex items-center justify-between mb-1.5 ml-1">
                                <label for="password" class="block text-xs font-bold text-[var(--color-text-main)] uppercase tracking-wider">Password</label>
                                <a href="#" class="text-xs font-bold text-[var(--color-primary)] hover:underline">Lupa password?</a>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[var(--color-text-muted)] group-focus-within:text-[var(--color-primary)] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input id="password" name="password" type="password" required 
                                    class="block w-full pl-12 pr-4 py-4 bg-gray-50/50 border border-gray-200 text-sm rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-[var(--color-primary)] focus:bg-white outline-none transition-all placeholder:text-gray-400" 
                                    placeholder="••••••••">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-[var(--color-primary)] focus:ring-[var(--color-primary)] border-gray-300 rounded-md cursor-pointer transition-all">
                        <label for="remember-me" class="ml-2 block text-sm text-[var(--color-text-muted)] font-medium cursor-pointer">
                            Ingat Saya
                        </label>
                    </div>

                    <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent text-sm font-bold rounded-2xl text-white bg-[var(--color-primary)] hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all shadow-xl shadow-blue-900/10 active:scale-[0.98]">
                        Masuk Dashboard
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                    <p class="text-sm text-[var(--color-text-muted)]">
                        Bukan Admin atau Guru? 
                        <a href="{{ route('student.login') }}" class="font-bold text-[var(--color-primary)] hover:underline ml-1">Login sebagai Siswa</a>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="text-center text-xs text-slate-400 mt-8 font-medium tracking-wide">
            &copy; 2025 CBT System &bull; SMAIT Baitul Muslim
        </div>
    </div>
</body>
</html>
