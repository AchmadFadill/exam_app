<div class="space-y-5">
    @if ($successMessage)
        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm font-medium">
            {{ $successMessage }}
        </div>
    @else
        @if ($errorMessage)
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium">
                {{ $errorMessage }}
            </div>
        @endif

        <form wire:submit.prevent="submit" class="space-y-5">
            <!-- Email Field -->
            <div class="space-y-2 group">
                <label for="email" class="block text-xs font-bold text-[var(--color-text-main)] uppercase tracking-[0.15em] ml-1 opacity-60 group-focus-within:opacity-100 transition-opacity">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[var(--color-text-muted)] transition-colors group-focus-within:text-[var(--color-primary)]">
                       <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path></svg>
                    </span>
                    <input wire:model="email" id="email" type="email" required
                        class="block w-full pl-12 pr-4 py-4 bg-gray-50/50 border border-gray-200 text-sm rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-[var(--color-primary)] focus:bg-white outline-none transition-all placeholder:text-gray-400 font-medium" 
                        placeholder="nama@sekolah.sch.id">
                </div>
            </div>

            <!-- Reason Field -->
            <div class="space-y-2 group">
                <label for="reason" class="block text-xs font-bold text-[var(--color-text-main)] uppercase tracking-[0.15em] ml-1 opacity-60 group-focus-within:opacity-100 transition-opacity">Alasan Reset</label>
                <textarea wire:model="reason" id="reason" rows="3" required
                    class="block w-full px-4 py-4 bg-gray-50/50 border border-gray-200 text-sm rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-[var(--color-primary)] focus:bg-white outline-none transition-all placeholder:text-gray-400 font-medium" 
                    placeholder="Contoh: Lupa password, tidak bisa login..."></textarea>
            </div>

            <x-button type="submit" variant="primary" class="w-full py-3.5 px-6 !rounded-2xl font-bold uppercase tracking-widest text-sm shadow-xl shadow-blue-900/20 active:scale-[0.98]">
                KIRIM PERMINTAAN
            </x-button>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-[var(--color-primary)] font-medium transition-colors">
                    &larr; Kembali ke Halaman Login Guru
                </a>
            </div>
        </form>
    @endif
</div>
