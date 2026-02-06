<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pengaturan Profil</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Photo Section -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Foto Profil</h2>
                
                @if (session('photo_success'))
                    <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 text-sm font-medium">
                        {{ session('photo_success') }}
                    </div>
                @endif

                <div class="flex flex-col items-center">
                    <!-- Photo Preview -->
                    <div class="relative w-32 h-32 mb-4">
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full rounded-full object-cover border-4 border-gray-50 shadow-md">
                        @else
                            <img src="{{ Auth::user()->profile_photo_url }}" class="w-full h-full rounded-full object-cover border-4 border-gray-50 shadow-md">
                        @endif
                        
                        <!-- Loading Indicator -->
                        <div wire:loading wire:target="photo" class="absolute inset-0 flex items-center justify-center bg-white/50 rounded-full">
                            <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="w-full space-y-3">
                        <!-- Upload Button -->
                        <div>
                            <input type="file" wire:model="photo" id="photo" class="hidden">
                            <label for="photo" class="block w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 text-center cursor-pointer hover:bg-gray-50 transition">
                                Pilih Foto Baru
                            </label>
                            @error('photo') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Save/Delete Actions -->
                        @if ($photo)
                            <button wire:click="savePhoto" class="w-full px-4 py-2 bg-[var(--color-primary)] text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                                Simpan Foto
                            </button>
                        @endif

                        @if (Auth::user()->profile_photo_path && !$photo)
                            <button wire:click="deletePhoto" wire:confirm="Hapus foto profil?" class="w-full px-4 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                                Hapus Foto
                            </button>
                        @endif
                    </div>

                    <p class="mt-4 text-xs text-gray-400 text-center">
                        Format: JPG, PNG. Maks: 1MB.
                    </p>
                </div>
            </div>
        </div>

        <!-- Password Change Section -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Ganti Password</h2>

                @if (session('password_success'))
                    <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ session('password_success') }}
                    </div>
                @endif

                <form wire:submit.prevent="updatePassword" class="space-y-5">
                    <!-- Current Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Password Saat Ini</label>
                        <input wire:model="current_password" type="password" 
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        @error('current_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- New Password -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Password Baru</label>
                            <input wire:model="password" type="password" 
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                            @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password</label>
                            <input wire:model="password_confirmation" type="password" 
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <x-button type="submit" variant="primary" class="px-6">
                            Simpan Password
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
