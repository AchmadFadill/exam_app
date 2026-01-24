@section('title', 'Pengaturan Sistem')

<div class="max-w-5xl mx-auto space-y-10 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-black text-4xl text-text-main tracking-tight italic">Global <span class="text-primary not-italic">Settings</span></h1>
            <p class="text-text-muted mt-2 font-medium">Konfigurasi pusat identitas dan parameter sistem CBT.</p>
        </div>
        <button wire:click="save" class="group bg-primary hover:bg-blue-700 text-white px-8 py-3.5 rounded-2xl font-bold transition-all flex items-center gap-3 shadow-xl shadow-primary/20 hover:-translate-y-0.5 active:translate-y-0">
            <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            Simpan Konfigurasi
        </button>
    </div>

    <div class="space-y-8 max-w-4xl mx-auto">
        <!-- Identitas Sekolah Card -->
        <div class="bg-bg-surface dark:bg-bg-surface border border-border-main dark:border-border-main rounded-[2rem] shadow-xl shadow-black/5 overflow-hidden transition-all">
            <div class="p-8 border-b border-border-subtle dark:border-border-subtle flex items-center gap-4 bg-gray-50/50 dark:bg-slate-800/30">
                <div class="w-12 h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <h3 class="font-black text-xl text-text-main tracking-tight uppercase">Identitas Institusi</h3>
                    <p class="text-text-muted text-xs font-bold tracking-widest leading-none mt-1 uppercase opacity-60">School Info & Branding</p>
                </div>
            </div>

            <div class="p-8 space-y-8">
                <div>
                    <label class="block text-sm font-black text-text-main mb-2 uppercase tracking-widest opacity-70">Nama Sekolah / Lembaga</label>
                    <input type="text" wire:model="schoolName" class="w-full px-6 py-4 rounded-2xl border-border-main dark:border-border-main dark:bg-slate-800/50 dark:text-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all font-bold text-lg" placeholder="Contoh: SMAIT Baitul Muslim">
                    @error('schoolName') <span class="text-red-500 text-xs mt-2 block font-bold leading-none">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-black text-text-main mb-3 uppercase tracking-widest opacity-70">Logo Signature</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative group">
                            <input type="file" wire:model="logo" id="logo-upload" class="hidden">
                            <label for="logo-upload" class="flex flex-col items-center justify-center h-48 border-2 border-dashed border-border-main dark:border-border-main rounded-[2rem] hover:border-primary hover:bg-primary/5 transition-all cursor-pointer group">
                                @if ($logo)
                                    <div class="relative w-full h-full p-4">
                                        <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-contain rounded-xl">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded-[1.8rem]">
                                            <span class="text-white text-xs font-bold uppercase tracking-widest">Ganti Logo</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center p-6">
                                        <div class="w-16 h-16 bg-bg-surface dark:bg-slate-800 rounded-full flex items-center justify-center mb-4 mx-auto border border-border-subtle group-hover:scale-110 transition-transform shadow-lg shadow-black/5">
                                            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        </div>
                                        <p class="text-text-main font-black text-sm uppercase tracking-tight">Upload Logo</p>
                                        <p class="text-text-muted text-[10px] mt-1 font-bold">PNG, JPG up to 2MB</p>
                                    </div>
                                @endif
                            </label>
                        </div>

                        <div class="bg-blue-50/50 dark:bg-primary/5 rounded-[2rem] p-6 border border-primary/10 flex items-center justify-center text-center">
                            <div class="max-w-[200px]">
                                <div class="w-10 h-10 bg-primary/20 text-primary rounded-xl flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h4 class="text-xs font-black text-text-main uppercase tracking-widest mb-2 leading-tight">Requirement</h4>
                                <p class="text-[11px] text-text-muted font-bold leading-relaxed">Gunakan background transparan (PNG) untuk hasil maksimal di sidebar.</p>
                            </div>
                        </div>
                    </div>
                    @error('logo') <span class="text-red-500 text-xs mt-2 block font-bold">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Akademik Card -->
        <div class="bg-bg-surface dark:bg-bg-surface border border-border-main dark:border-border-main rounded-[2rem] shadow-xl shadow-black/5 overflow-hidden">
            <div class="p-8 border-b border-border-subtle dark:border-border-subtle flex items-center gap-4 bg-gray-50/50 dark:bg-slate-800/30">
                <div class="w-12 h-12 bg-amber-500/10 text-amber-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h3 class="font-black text-xl text-text-main tracking-tight uppercase">Parameter Akademik</h3>
                    <p class="text-text-muted text-xs font-bold tracking-widest leading-none mt-1 uppercase opacity-60">Defaults & Schedules</p>
                </div>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-sm font-black text-text-main mb-2 uppercase tracking-widest opacity-70">Tahun Ajaran Aktif</label>
                        <input type="text" wire:model="academicYear" class="w-full px-6 py-4 rounded-2xl border-border-main dark:border-border-main dark:bg-slate-800/50 dark:text-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all font-bold" placeholder="2025/2026">
                        @error('academicYear') <span class="text-red-500 text-xs mt-2 block font-bold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-black text-text-main mb-2 uppercase tracking-widest opacity-70">Semester Default</label>
                        <select wire:model="semester" class="w-full px-6 py-4 rounded-2xl border-border-main dark:border-border-main dark:bg-slate-800/50 dark:text-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all font-bold appearance-none bg-no-repeat bg-[right_1.5rem_center] bg-[length:1em_1em]" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22currentColor%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E')">
                            <option value="Ganjil">Semester Ganjil</option>
                            <option value="Genap">Semester Genap</option>
                        </select>
                    </div>
                </div>
                <p class="mt-6 text-xs text-text-muted font-bold flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    Setting ini akan menjadi filter default pada dashboard dan laporan.
                </p>
            </div>
        </div>
    </div>
</div>
