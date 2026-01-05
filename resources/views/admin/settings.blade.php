@section('title', 'Pengaturan Sistem')

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-bold text-2xl text-text-main">Pengaturan Sistem</h2>
            <p class="text-text-muted text-sm">Kelola identitas sekolah dan konfigurasi umum sistem</p>
        </div>
        <button wire:click="save" class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            Simpan Perubahan
        </button>
    </div>

    <!-- Identitas Sekolah -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-lg text-text-main mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            Identitas Sekolah
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-text-main mb-1">Nama Sekolah</label>
                    <input type="text" wire:model="schoolName" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20 transition-shadow">
                    @error('schoolName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-main mb-1">Logo Sekolah</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="space-y-1 text-center">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" class="mx-auto h-12 w-auto mb-2">
                            @else
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            @endif
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="logo-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-blue-700 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                    <span>Upload file logo</span>
                                    <input id="logo-upload" wire:model="logo" type="file" class="sr-only">
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG up to 1MB</p>
                        </div>
                    </div>
                     @error('logo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 h-full flex items-center justify-center border border-gray-200">
                <div class="text-center">
                    <p class="text-xs text-text-muted mb-2 uppercase font-bold tracking-wider">Preview Sidebar</p>
                    <div class="bg-primary text-white w-48 h-20 rounded-lg flex items-center justify-center flex-col shadow-lg mx-auto">
                        <div class="flex items-center gap-2">
                            <div class="bg-white/20 p-1 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <div class="text-left leading-none">
                                <div class="font-bold text-xs">SMAIT CBT</div>
                                <div class="text-[10px] opacity-80">{{ $schoolName ?: 'Nama Sekolah' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Pengaturan Akademik -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
             <h3 class="font-bold text-lg text-text-main mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Akademik
            </h3>
            <div>
                <label class="block text-sm font-medium text-text-main mb-1">Tahun Ajaran / Semester</label>
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" wire:model="academicYear" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20 transition-shadow" placeholder="Contoh: 2025/2026">
                    <select wire:model="semester" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20 transition-shadow">
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                </div>
                <p class="text-xs text-text-muted mt-1">Digunakan sebagai default untuk semua ujian baru.</p>
                @error('academicYear') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>


    </div>
</div>
