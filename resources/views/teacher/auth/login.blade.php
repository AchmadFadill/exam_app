<x-layouts.auth-simple title="Portal Guru" action="{{ route('teacher.login') }}">
    <div class="space-y-4">
        <!-- Email Field -->
        <div class="space-y-1.5 group">
            <label for="email" class="block text-[10px] font-bold text-[var(--color-text-main)] uppercase tracking-[0.15em] ml-1 opacity-60 group-focus-within:opacity-100 transition-opacity">Email Address</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[var(--color-text-muted)] transition-colors group-focus-within:text-[var(--color-primary)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                    </svg>
                </span>
                <input id="email" name="email" type="email" required value="{{ old('email') }}"
                    class="block w-full pl-12 pr-4 py-3.5 bg-gray-50/50 border border-gray-200 text-sm rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-[var(--color-primary)] focus:bg-white outline-none transition-all placeholder:text-gray-400 font-medium @error('email') border-red-300 @enderror" 
                    placeholder="nama@sekolah.sch.id">
            </div>
        </div>

        <!-- Password Field -->
        <div class="space-y-1.5 group">
            <div class="flex items-center justify-between ml-1">
                <label for="password" class="block text-[10px] font-bold text-[var(--color-text-main)] uppercase tracking-[0.15em] opacity-60 group-focus-within:opacity-100 transition-opacity">Password</label>
                <a href="{{ route('teacher.password-reset') }}" class="text-[10px] font-bold text-[var(--color-primary)] hover:underline transition-colors">Lupa password?</a>
            </div>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[var(--color-text-muted)] transition-colors group-focus-within:text-[var(--color-primary)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </span>
                <input id="password" name="password" type="password" required 
                    class="block w-full pl-12 pr-12 py-3.5 bg-gray-50/50 border border-gray-200 text-sm rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-[var(--color-primary)] focus:bg-white outline-none transition-all placeholder:text-gray-400 font-medium anchor-password" 
                    placeholder="••••••••">
                <button type="button" onclick="togglePassword('password', 'password-icon')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-[var(--color-primary)] transition-colors">
                    <svg id="password-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path class="eye-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path class="eye-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        <path class="eye-closed hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057-5.064-7 9.542-7 1.222 0 2.391.22 3.474.625M19.74 19.74A12.401 12.401 0 0112 21c-4.478 0-8.268-2.943-9.542-7 1.274-4.057-5.064-7 9.542-7 .92 0 1.817.11 2.68.315M15 12a3 3 0 11-6 0 3 3 0 016 0zM3 3l18 18" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="flex items-center px-1">
        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-[var(--color-primary)] focus:ring-[var(--color-primary)]/10 border-gray-300 rounded-lg cursor-pointer transition-all">
        <label for="remember" class="ml-2.5 block text-xs text-[var(--color-text-muted)] font-semibold cursor-pointer select-none">
            Ingat Saya
        </label>
    </div>

    <x-button type="submit" variant="primary" class="w-full py-3.5 px-6 !rounded-2xl font-bold uppercase tracking-widest text-sm shadow-xl shadow-blue-900/20 active:scale-[0.98]">
        MASUK DASHBOARD
    </x-button>

    <x-slot:footer>
        <x-button href="{{ route('student.login') }}" variant="secondary" size="xs" class="flex-1 !rounded-xl !bg-blue-50 !text-blue-700 !border-blue-100 hover:!bg-blue-100">
            Siswa
        </x-button>
        <x-button href="{{ route('login') }}" variant="secondary" size="xs" class="flex-1 !rounded-xl !bg-gray-50 !text-gray-700 !border-gray-100 hover:!bg-gray-100">
            Admin
        </x-button>
    </x-slot:footer>
</x-layouts.auth-simple>
