@section('title', $examId ? 'Edit Ujian' : 'Jadwalkan Ujian Baru')

<div class="max-w-5xl mx-auto space-y-10 pb-20">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
        <div class="flex items-center gap-6">
            <a href="{{ route('teacher.exams.index') }}" class="group p-4 bg-bg-surface dark:bg-slate-800 rounded-2xl border border-border-main dark:border-border-main text-text-muted hover:text-primary transition-all shadow-sm hover:shadow-md active:scale-95">
                <svg class="w-6 h-6 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-black text-3xl text-text-main tracking-tight uppercase italic leading-none">
                    {{ $examId ? 'Edit Sesi' : 'Jadwalkan' }} <span class="text-primary not-italic">Ujian</span>
                </h2>
                <p class="text-[10px] font-black text-text-muted uppercase tracking-[0.2em] mt-3 opacity-60">Konfigurasi parameter dan jadwal pelaksanaan ujian</p>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="flex items-center gap-4 bg-bg-surface dark:bg-slate-900 px-8 py-4 rounded-[2rem] border border-border-main dark:border-border-main shadow-inner">
            <div class="flex flex-col items-center gap-1">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black transition-all duration-500 {{ $step >= 1 ? 'bg-primary text-white shadow-lg shadow-primary/30 scale-110' : 'bg-gray-100 dark:bg-slate-800 text-text-muted' }} italic">
                    01
                </div>
                <span class="text-[9px] font-black uppercase tracking-widest {{ $step >= 1 ? 'text-primary' : 'text-text-muted opacity-40' }}">Detail</span>
            </div>
            
            <div class="w-16 h-1 rounded-full bg-gray-100 dark:bg-slate-800 overflow-hidden">
                <div class="h-full bg-primary transition-all duration-700 ease-out" style="width: {{ $step >= 2 ? '100%' : '0%' }}"></div>
            </div>

            <div class="flex flex-col items-center gap-1">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black transition-all duration-500 {{ $step >= 2 ? 'bg-primary text-white shadow-lg shadow-primary/30 scale-110' : 'bg-gray-100 dark:bg-slate-800 text-text-muted' }} italic">
                    02
                </div>
                <span class="text-[9px] font-black uppercase tracking-widest {{ $step >= 2 ? 'text-primary' : 'text-text-muted opacity-40' }}">Soal</span>
            </div>
        </div>
    </div>

    <div class="relative">
        <form wire:submit.prevent="save" class="space-y-10">
            
            <!-- Step 1: Basic Info & Settings -->
            @if($step === 1)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <!-- Left Column: Primary Details -->
                <div class="lg:col-span-2 space-y-10">
                    <x-card title="Informasi Dasar">
                        <x-slot name="header_actions">
                            <span class="text-[10px] font-black text-primary bg-primary/10 px-3 py-1 rounded-full uppercase tracking-widest">Wajib diisi</span>
                        </x-slot>

                        <div class="space-y-8">
                            <div>
                                <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Nama Sesi Ujian</label>
                                <input type="text" wire:model="name" class="w-full px-6 py-5 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold text-lg tracking-tight shadow-inner" placeholder="Contoh: Ujian Tengah Semester Ganjil">
                                @error('name') <p class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                                <div>
                                    <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Mata Pelajaran</label>
                                    <div class="relative group">
                                        <select wire:model="subject" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold appearance-none bg-no-repeat bg-[right_1.5rem_center] bg-[length:1em_1em] shadow-inner" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22currentColor%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222.5%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E')">
                                            <option value="">Pilih Mata Pelajaran</option>
                                            <option value="Matematika">Matematika</option>
                                            <option value="Biologi">Biologi</option>
                                            <option value="Sejarah">Sejarah</option>
                                            <option value="Fisika">Fisika</option>
                                        </select>
                                    </div>
                                    @error('subject') <p class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Passing Grade (KKM)</label>
                                    <div class="relative">
                                        <input type="number" wire:model="passing_grade" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold shadow-inner" placeholder="70">
                                        <span class="absolute right-6 top-1/2 -translate-y-1/2 text-xs font-black text-text-muted opacity-40 uppercase tracking-widest">Poin</span>
                                    </div>
                                    @error('passing_grade') <p class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </x-card>

                    <x-card title="Jadwal Pelaksanaan">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                            <div class="sm:col-span-2 lg:col-span-1">
                                <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Tanggal Ujian</label>
                                <input type="date" wire:model="date" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold shadow-inner">
                                @error('date') <p class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Waktu Mulai</label>
                                <input type="time" wire:model="start_time" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold shadow-inner">
                                @error('start_time') <p class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Waktu Selesai</label>
                                <input type="time" wire:model="end_time" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold shadow-inner">
                                @error('end_time') <p class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2 lg:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-8">
                                <div>
                                    <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Durasi Pengerjaan</label>
                                    <div class="relative">
                                        <input type="number" wire:model="duration" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold shadow-inner" placeholder="90">
                                        <span class="absolute right-6 top-1/2 -translate-y-1/2 text-xs font-black text-text-muted opacity-40 uppercase tracking-widest">Menit</span>
                                    </div>
                                    @error('duration') <p class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Bobot Poin Default</label>
                                    <div class="relative">
                                        <input type="number" wire:model="default_score" class="w-full px-6 py-4 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold shadow-inner" placeholder="5">
                                        <span class="absolute right-6 top-1/2 -translate-y-1/2 text-xs font-black text-text-muted opacity-40 uppercase tracking-widest">Per Soal</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-card>

                    <x-card title="Kelas Peserta">
                        <x-slot name="header_actions">
                            <div class="flex gap-2">
                                <button type="button" wire:click="toggleLevel('X')" class="group relative px-4 py-2 bg-primary/5 hover:bg-primary/10 rounded-xl transition-all overflow-hidden border border-primary/10">
                                    <span class="relative z-10 text-[9px] font-black text-primary uppercase tracking-widest">Pilih Semua X</span>
                                    <div class="absolute inset-0 bg-primary opacity-0 group-active:opacity-10 transition-opacity"></div>
                                </button>
                                <button type="button" wire:click="toggleLevel('XI')" class="group relative px-4 py-2 bg-primary/5 hover:bg-primary/10 rounded-xl transition-all overflow-hidden border border-primary/10">
                                    <span class="relative z-10 text-[9px] font-black text-primary uppercase tracking-widest">Pilih Semua XI</span>
                                    <div class="absolute inset-0 bg-primary opacity-0 group-active:opacity-10 transition-opacity"></div>
                                </button>
                                <button type="button" wire:click="toggleLevel('XII')" class="group relative px-4 py-2 bg-primary/5 hover:bg-primary/10 rounded-xl transition-all overflow-hidden border border-primary/10">
                                    <span class="relative z-10 text-[9px] font-black text-primary uppercase tracking-widest">Pilih Semua XII</span>
                                    <div class="absolute inset-0 bg-primary opacity-0 group-active:opacity-10 transition-opacity"></div>
                                </button>
                            </div>
                        </x-slot>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach($availableClasses as $class)
                            <label class="relative flex flex-col items-center justify-center p-6 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-[2rem] cursor-pointer group transition-all hover:scale-[1.05] active:scale-95 shadow-sm hover:shadow-xl hover:shadow-primary/5">
                                <input type="checkbox" wire:model="classes" value="{{ $class }}" class="peer absolute inset-0 opacity-0 cursor-pointer">
                                
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-3 transition-all duration-300 {{ in_array($class, $classes) ? 'bg-primary text-white scale-110 shadow-lg shadow-primary/30' : 'bg-white dark:bg-slate-800 text-text-muted opacity-40 shadow-inner' }}">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest transition-colors duration-300 {{ in_array($class, $classes) ? 'text-text-main' : 'text-text-muted opacity-60' }}">{{ $class }}</span>
                                
                                <div class="absolute inset-0 rounded-[2rem] ring-2 ring-primary ring-offset-4 dark:ring-offset-slate-900 opacity-0 transition-opacity peer-checked:opacity-100"></div>
                            </label>
                            @endforeach
                        </div>
                        @error('classes') <p class="mt-4 text-[10px] font-bold text-red-500 uppercase tracking-widest text-center">{{ $message }}</p> @enderror
                    </x-card>
                </div>

                <!-- Right Column: Security & Token -->
                <div class="space-y-10">
                    <x-card title="Kode Keamanan" color="amber">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-black text-text-main mb-3 uppercase tracking-widest opacity-70 italic">Token Masuk Ujian</label>
                                <div class="relative group">
                                    <input type="text" wire:model="token" readonly class="w-full px-2 py-6 bg-slate-900 text-white border-2 border-slate-800 rounded-[2.5rem] focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all font-black text-2xl tracking-[0.3em] uppercase italic text-center shadow-2xl" placeholder="TOKEN">
                                    <button type="button" wire:click="regenerateToken" class="absolute top-1/2 -translate-y-1/2 right-2 p-3 bg-amber-500 hover:bg-amber-600 text-white rounded-2xl shadow-xl shadow-amber-500/30 transition-all hover:scale-110 active:rotate-180 group/btn" title="Regenerate Token">
                                        <svg class="w-4 h-4 transition-transform group-hover/btn:rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </button>
                                </div>
                                <p class="text-[9px] font-black text-text-muted uppercase tracking-[0.2em] mt-5 leading-relaxed opacity-60 px-2 text-center">Berikan kode ini kepada siswa tepat saat ujian dimulai untuk menjaga keamanan.</p>
                            </div>
                        </div>
                    </x-card>

                    <x-card title="Fitur Keamanan">
                        <div class="space-y-4">
                            <!-- Toggle Card: Shuffle Questions -->
                            <label class="relative flex items-center justify-between p-6 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-[2rem] cursor-pointer group hover:bg-primary/5 hover:border-primary/20 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 shadow-sm group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-text-main">Acak Soal</span>
                                </div>
                                <input type="checkbox" wire:model="shuffle_questions" class="hidden peer">
                                <div class="w-12 h-6 bg-gray-300 dark:bg-slate-800 rounded-full relative transition-colors peer-checked:bg-primary">
                                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-6 shadow-md"></div>
                                </div>
                            </label>

                            <!-- Toggle Card: Shuffle Answers -->
                            <label class="relative flex items-center justify-between p-6 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-[2rem] cursor-pointer group hover:bg-primary/5 hover:border-primary/20 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 shadow-sm group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-text-main">Acak Jawaban</span>
                                </div>
                                <input type="checkbox" wire:model="shuffle_answers" class="hidden peer">
                                <div class="w-12 h-6 bg-gray-300 dark:bg-slate-800 rounded-full relative transition-colors peer-checked:bg-primary">
                                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-6 shadow-md"></div>
                                </div>
                            </label>

                            <!-- Anti Tab-Switching Toggle -->
                            <label class="relative flex items-center justify-between p-6 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-[2rem] cursor-pointer group hover:bg-primary/5 hover:border-primary/20 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 shadow-sm group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-text-main">Anti Tab-Switching</span>
                                        <span class="text-[8px] font-bold text-text-muted uppercase tracking-tighter mt-1">Otomatis submit jika curang</span>
                                    </div>
                                </div>
                                <input type="checkbox" wire:model.live="enable_tab_tolerance" class="hidden peer">
                                <div class="w-12 h-6 bg-gray-300 dark:bg-slate-800 rounded-full relative transition-colors peer-checked:bg-primary">
                                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-6 shadow-md"></div>
                                </div>
                            </label>

                            <!-- Tab Tolerance Slider (Visible only if enabled) -->
                            <div x-show="$wire.enable_tab_tolerance" x-cloak x-transition class="p-8 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-[2.5rem] transition-all">
                                <div class="flex justify-between items-center mb-6">
                                    <label class="text-[10px] font-black uppercase tracking-widest opacity-70">Toleransi Pelanggaran</label>
                                    <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-[0.1em] bg-primary/10 text-primary border border-primary/20 shadow-inner">
                                        {{ $tab_tolerance }}x Kesempatan
                                    </span>
                                </div>
                                <div class="relative flex items-center gap-4 pt-2">
                                    <input type="range" wire:model.live="tab_tolerance" min="0" max="10" class="w-full h-1.5 bg-gray-300 dark:bg-slate-800 rounded-full appearance-none cursor-pointer accent-primary shadow-inner">
                                    <div class="flex justify-between absolute -bottom-7 w-full px-1">
                                        <span class="text-[8px] font-black text-text-muted uppercase tracking-widest opacity-40">Ketat (0)</span>
                                        <span class="text-[8px] font-black text-text-muted uppercase tracking-widest opacity-40">Longgar (10)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>
            @endif

            <!-- Step 2: Question Selection -->
            @if($step === 2)
            <div class="space-y-10">
                <!-- Selection Status Card -->
                <div class="bg-primary p-12 rounded-[3.5rem] shadow-2xl shadow-primary/20 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 -m-10 w-64 h-64 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
                        <div class="text-center md:text-left">
                            <h3 class="text-white text-4xl font-black tracking-tighter italic">Pilih Matrix <span class="text-blue-200 not-italic">Pertanyaan</span></h3>
                            <p class="text-blue-100/60 text-[10px] font-black uppercase tracking-[0.3em] mt-3">Silahkan pilih soal yang akan diujikan pada sesi ini</p>
                        </div>
                        <div class="flex items-center gap-8 bg-white/10 backdrop-blur-md px-10 py-6 rounded-[2.5rem] border border-white/10 shadow-inner">
                            <div class="text-center">
                                <div class="text-4xl font-black text-white italic">0</div>
                                <div class="text-[9px] font-black text-blue-200/60 uppercase tracking-widest mt-1">Soal Terpilih</div>
                            </div>
                            <div class="w-px h-12 bg-white/10"></div>
                            <div class="text-center">
                                <div class="text-4xl font-black text-white italic">0</div>
                                <div class="text-[9px] font-black text-blue-200/60 uppercase tracking-widest mt-1">Total Poin</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
                    <!-- Filters Column -->
                    <div class="lg:col-span-1 space-y-8">
                        <x-card title="Data Filter">
                            <div class="space-y-8">
                                <div>
                                    <label class="block text-[10px] font-black text-text-muted mb-3 uppercase tracking-widest">Mata Pelajaran</label>
                                    <select wire:model="filterSubject" class="w-full px-5 py-3.5 bg-gray-100/50 dark:bg-slate-900 border border-border-main dark:border-border-main rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold appearance-none bg-no-repeat bg-[right_1.2rem_center] bg-[length:0.8em_0.8em]" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22currentColor%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222.5%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E')">
                                        <option value="">Semua Mapel</option>
                                        <option value="Matematika">Matematika</option>
                                        <option value="Biologi">Biologi</option>
                                        <option value="Fisika">Fisika</option>
                                    </select>
                                </div>
                                
                                <div class="pt-8 border-t border-border-subtle dark:border-slate-800">
                                    <x-button type="button" variant="soft" class="w-full py-4 text-[10px]">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                        Buat Soal Instan
                                    </x-button>
                                </div>
                            </div>
                        </x-card>
                    </div>

                    <!-- Question List Column -->
                    <div class="lg:col-span-3 space-y-6">
                        <div class="relative group">
                            <input type="text" class="w-full pl-16 pr-10 py-6 bg-bg-surface dark:bg-slate-900 border border-border-main dark:border-border-main rounded-[2.5rem] shadow-xl shadow-black/5 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all font-bold text-lg tracking-tight" placeholder="Cari soal spesifik dari database bank soal...">
                            <svg class="w-6 h-6 text-text-muted group-focus-within:text-primary transition-colors absolute left-6 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>

                        <div class="space-y-4">
                            @for($i = 1; $i <= 5; $i++)
                            <label class="relative flex items-start gap-8 p-8 bg-bg-surface dark:bg-slate-900 border border-border-main dark:border-border-main rounded-[3rem] cursor-pointer group hover:border-primary/40 hover:shadow-2xl hover:shadow-primary/5 hover:-translate-y-1 transition-all duration-300">
                                <input type="checkbox" class="hidden peer">
                                
                                <div class="mt-1 w-8 h-8 rounded-xl border-2 border-border-main dark:border-slate-800 flex items-center justify-center transition-all peer-checked:bg-primary peer-checked:border-primary peer-checked:scale-110 shadow-inner group-hover:border-primary/20">
                                    <svg class="w-5 h-5 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>

                                <div class="flex-1 space-y-4">
                                    <div class="flex items-center gap-4">
                                        <span class="px-3 py-1 rounded-full bg-primary/10 text-primary text-[9px] font-black uppercase tracking-[0.2em] shadow-inner">Pilihan Ganda</span>
                                        <span class="w-1.5 h-1.5 rounded-full bg-border-main"></span>
                                        <span class="text-[9px] font-black text-text-muted uppercase tracking-[0.2em] opacity-40 italic">#MATRIX_{{ 2548 + $i }}</span>
                                    </div>
                                    <p class="text-lg font-bold text-text-main tracking-tight leading-relaxed group-hover:text-primary transition-colors italic">"Ini adalah contoh konten pertanyaan ke {{ $i }} yang diambil secara dinamis dari infrastruktur bank soal anda..."</p>
                                    <div class="flex items-center gap-6 pt-4 border-t border-border-subtle dark:border-slate-800 opacity-40 group-hover:opacity-100 transition-opacity">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                            <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">10 Poin</span>
                                        </div>
                                         <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">Sulit</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="absolute inset-x-0 bottom-0 h-1.5 bg-primary transform scale-x-0 transition-transform duration-500 rounded-b-[3rem] peer-checked:scale-x-100"></div>
                            </label>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Form Actions -->
            <div class="flex items-center justify-between p-10 bg-bg-surface dark:bg-slate-900 border border-border-main dark:border-border-main rounded-[3rem] shadow-2xl relative overflow-hidden group">
                <div class="absolute inset-0 bg-primary/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                
                @if($step > 1)
                    <button type="button" wire:click="prevStep" class="relative z-10 px-8 py-4 bg-white dark:bg-slate-800 border border-border-main dark:border-border-main rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] text-text-muted hover:text-primary hover:border-primary/20 hover:-translate-x-2 transition-all active:scale-95 shadow-sm">
                        &larr; Tahap Sebelumnya
                    </button>
                @else
                    <a href="{{ route('teacher.exams.index') }}" class="relative z-10 px-8 py-4 bg-white/50 dark:bg-slate-800/50 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] text-text-muted hover:text-red-500 transition-all">Batal & Keluar</a>
                @endif
                
                @if($step < 2)
                    <button type="button" wire:click="nextStep" class="relative z-10 px-10 py-5 bg-primary hover:bg-blue-600 text-white rounded-[1.5rem] font-black text-xs uppercase tracking-[0.2em] transition-all hover:scale-[1.05] hover:rotate-3 shadow-2xl shadow-primary/30 flex items-center gap-3 active:scale-95">
                        Lanjut: Pilih Matrix Soal
                        <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                @else
                    <button type="submit" class="relative z-10 px-10 py-5 bg-green-600 hover:bg-green-700 text-white rounded-[1.5rem] font-black text-xs uppercase tracking-[0.2em] transition-all hover:scale-[1.05] hover:-rotate-3 shadow-2xl shadow-green-600/30 flex items-center gap-3 active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        Simpan & Jadwalkan
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>


