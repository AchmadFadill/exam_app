<div x-on:confirmed-delete-photo.window="$wire.deletePhoto()">
    <x-header 
        title="Pengaturan Profil" 
        subtitle="Kelola Profil Anda" 
    />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 ">
        <!-- Profile Photo Section -->
        <div class="lg:col-span-4">
            <x-card title="Foto Profil">
                <div class="flex flex-col items-center">
                    <!-- Photo Container -->
                    <div class="relative group/avatar">
                        <div class="relative w-40 h-40 rounded-full overflow-hidden ring-4 ring-gray-50 dark:ring-slate-800 group-hover/avatar:ring-primary/20 transition-all duration-300 shadow-inner">
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover transform scale-105 group-hover/avatar:scale-100 transition-transform duration-500">
                            @else
                                <img src="{{ Auth::user()->profile_photo_url }}" class="w-full h-full object-cover transform scale-105 group-hover/avatar:scale-100 transition-transform duration-500">
                            @endif
                            
                            <div wire:loading wire:target="photo" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-slate-900/90 backdrop-blur-md z-30 transition-all">
                                <div class="flex flex-col items-center justify-center w-full h-full gap-3 p-4">
                                    <div class="relative flex items-center justify-center">
                                        <div class="absolute w-12 h-12 rounded-full border-4 border-primary/20 animate-pulse"></div>
                                        <svg class="animate-spin h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <span class="text-[10px] font-black text-primary uppercase tracking-[0.2em] animate-pulse">Memproses</span>
                                        <span class="text-[8px] font-bold text-text-muted uppercase tracking-widest mt-0.5 opacity-60">Mohon Tunggu</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Overlay Trigger -->
                        <label for="photo" class="absolute bottom-1 right-1 bg-bg-surface dark:bg-slate-800 p-2.5 rounded-2xl shadow-xl border border-border-main dark:border-slate-700 cursor-pointer text-text-muted hover:text-primary hover:scale-110 active:scale-95 transition-all duration-200 z-20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <input type="file" wire:model="photo" id="photo" class="hidden" accept="image/*">
                        </label>
                    </div>

                    <div class="mt-8 w-full space-y-4">
                        @if (session('photo_success'))
                            <div class="p-4 rounded-2xl bg-green-500/10 border border-green-500/20 text-green-600 dark:text-green-400 text-xs font-bold flex items-center gap-3 animate-slideDown">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                {{ session('photo_success') }}
                            </div>
                        @endif

                        @if ($photo)
                            <div class="flex gap-3">
                                <x-button wire:click="savePhoto" class="flex-1 animate-pulse-subtle">
                                    Simpan
                                </x-button>
                                <x-button variant="secondary" @click="$wire.set('photo', null)" class="flex-1">
                                    Batal
                                </x-button>
                            </div>
                        @endif

                        @if (Auth::user()->profile_photo_path && !$photo)
                            <x-button variant="danger" 
                                @click="$dispatch('show-confirm-modal', [{ 
                                    title: 'Hapus Foto Profil?', 
                                    message: 'Apakah Anda yakin ingin menghapus foto profil Anda? Tindakan ini tidak dapat dibatalkan.', 
                                    confirmText: 'Ya, Hapus', 
                                    type: 'danger', 
                                    onConfirm: 'delete-photo' 
                                }])" 
                                class="w-full">
                                Hapus Foto Profil
                            </x-button>
                        @endif

                        <div class="text-center pt-2">
                            <p class="text-[10px] font-black text-text-muted uppercase tracking-[0.2em] opacity-40">Format JPG, PNG (Max 1MB)</p>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Password Change Section -->
        <div class="lg:col-span-8">
            <x-card title="Ganti Kata Sandi" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
                @if (session('password_success'))
                    <div class="mb-8 p-6 rounded-[2rem] bg-green-500/10 border border-green-500/20 text-green-600 dark:text-green-400 flex items-center gap-5 animate-slideDown">
                        <div class="w-12 h-12 rounded-2xl bg-green-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div class="text-sm font-black tracking-tight uppercase">
                            {{ session('password_success') }}
                        </div>
                    </div>
                @endif

                <form wire:submit.prevent="updatePassword" class="space-y-8">
                    <div class="grid grid-cols-1 gap-8">
                        <!-- Current Password -->
                        <div class="group/input">
                            <label class="block text-[10px] font-black text-text-muted uppercase tracking-[0.2em] mb-3 px-1 group-focus-within/input:text-primary transition-colors">Kata Sandi Saat Ini</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-text-muted group-focus-within/input:text-primary transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                                <input wire:model="current_password" :type="showCurrent ? 'text' : 'password'" 
                                    placeholder="Masukkan kata sandi lama"
                                    class="w-full pl-14 pr-14 py-4 bg-gray-50 dark:bg-slate-800/50 border border-border-main dark:border-slate-700 rounded-2xl focus:bg-white dark:focus:bg-slate-800 focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all font-bold text-text-main placeholder:text-text-muted placeholder:opacity-30">
                                <button type="button" @click="showCurrent = !showCurrent" class="absolute right-4 top-1/2 -translate-y-1/2 p-2 text-text-muted hover:text-primary transition-colors focus:outline-none">
                                    <template x-if="!showCurrent">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </template>
                                    <template x-if="showCurrent">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                                    </template>
                                </button>
                            </div>
                            @error('current_password') <span class="text-[10px] font-black text-red-500 mt-2 block uppercase tracking-widest px-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- New Password Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/input">
                                <label class="block text-[10px] font-black text-text-muted uppercase tracking-[0.2em] mb-3 px-1 group-focus-within/input:text-primary transition-colors">Kata Sandi Baru</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-text-muted group-focus-within/input:text-primary transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    </div>
                                    <input wire:model="password" :type="showNew ? 'text' : 'password'" 
                                        placeholder="Min. 8 karakter"
                                        class="w-full pl-14 pr-14 py-4 bg-gray-50 dark:bg-slate-800/50 border border-border-main dark:border-slate-700 rounded-2xl focus:bg-white dark:focus:bg-slate-800 focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all font-bold text-text-main placeholder:text-text-muted placeholder:opacity-30">
                                    <button type="button" @click="showNew = !showNew" class="absolute right-4 top-1/2 -translate-y-1/2 p-2 text-text-muted hover:text-primary transition-colors focus:outline-none">
                                        <template x-if="!showNew">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </template>
                                        <template x-if="showNew">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                                        </template>
                                    </button>
                                </div>
                                @error('password') <span class="text-[10px] font-black text-red-500 mt-2 block uppercase tracking-widest px-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="group/input">
                                <label class="block text-[10px] font-black text-text-muted uppercase tracking-[0.2em] mb-3 px-1 group-focus-within/input:text-primary transition-colors">Konfirmasi Sandi</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-text-muted group-focus-within/input:text-primary transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    </div>
                                    <input wire:model="password_confirmation" :type="showConfirm ? 'text' : 'password'" 
                                        placeholder="Ulangi kata sandi baru"
                                        class="w-full pl-14 pr-14 py-4 bg-gray-50 dark:bg-slate-800/50 border border-border-main dark:border-slate-700 rounded-2xl focus:bg-white dark:focus:bg-slate-800 focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all font-bold text-text-main placeholder:text-text-muted placeholder:opacity-30">
                                    <button type="button" @click="showConfirm = !showConfirm" class="absolute right-4 top-1/2 -translate-y-1/2 p-2 text-text-muted hover:text-primary transition-colors focus:outline-none">
                                        <template x-if="!showConfirm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </template>
                                        <template x-if="showConfirm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                                        </template>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 flex justify-end">
                        <x-button type="submit" size="lg" class="px-10">
                            Perbarui Kata Sandi
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse-subtle {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.85; }
        }
        .animate-fadeIn { animation: fadeIn 0.5s ease-out forwards; }
        .animate-slideDown { animation: slideDown 0.3s ease-out forwards; }
        .animate-pulse-subtle { animation: pulse-subtle 2s infinite ease-in-out; }
    </style>
</div>
