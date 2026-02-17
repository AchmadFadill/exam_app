<x-layouts.auth-simple title="Ganti Password Admin" action="{{ route('admin.password.force.update') }}">
    <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200">
        <p class="text-[12px] font-semibold text-amber-800">
            Demi keamanan, Anda wajib mengganti password sebelum melanjutkan.
        </p>
    </div>

    <div class="space-y-1.5 group">
        <label for="password" class="block text-[10px] font-bold uppercase tracking-[0.15em] ml-1 opacity-70">
            Password Baru
        </label>
        <input
            id="password"
            name="password"
            type="password"
            required
            class="block w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 text-sm rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-[var(--color-primary)] focus:bg-white outline-none transition-all placeholder:text-gray-400 font-medium @error('password') border-red-300 @enderror"
            placeholder="Minimal 8 karakter"
        >
        @error('password') <span class="text-red-500 text-[10px] mt-1 block font-bold">{{ $message }}</span> @enderror
    </div>

    <div class="space-y-1.5 group">
        <label for="password_confirmation" class="block text-[10px] font-bold uppercase tracking-[0.15em] ml-1 opacity-70">
            Konfirmasi Password
        </label>
        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            required
            class="block w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 text-sm rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-[var(--color-primary)] focus:bg-white outline-none transition-all placeholder:text-gray-400 font-medium"
            placeholder="Ulangi password baru"
        >
    </div>

    <x-button type="submit" variant="primary" class="w-full py-3.5 px-6 !rounded-2xl font-bold uppercase tracking-widest text-sm">
        Simpan Password Baru
    </x-button>
</x-layouts.auth-simple>
