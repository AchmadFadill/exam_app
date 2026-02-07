@section('title', 'Kelola Ujian')

<div class="space-y-6">
    <div class="mb-12 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6">
    <x-header 
        title="Daftar Ujian" 
        subtitle="Manajemen Pelaksanaan Ujian" 
    />
        <div>
            <a href="{{ route('teacher.exams.create') }}" class="group inline-flex items-center gap-4 bg-primary hover:bg-blue-700 text-white px-8 py-3.5 rounded-[2rem] text-sm font-black transition-all shadow-xl shadow-primary/20 uppercase tracking-widest">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Buat Ujian Baru
            </a>
        </div>
    </div>

    <!-- Exam Filter/Select All -->
    <div class="flex items-center gap-4 mb-8">
        <div class="flex items-center gap-3 px-6 py-3 bg-bg-surface dark:bg-slate-800 border border-border-main dark:border-slate-700 rounded-2xl shadow-sm">
            <input type="checkbox" wire:model.live="selectAll" id="selectAllExams" class="w-5 h-5 rounded-lg text-primary border-border-main dark:border-slate-600 focus:ring-primary/20 bg-bg-surface dark:bg-slate-900">
            <label for="selectAllExams" class="text-[10px] font-black text-text-muted uppercase tracking-[0.2em] cursor-pointer">Pilih Semua</label>
        </div>
        <div class="h-6 w-px bg-border-subtle dark:bg-slate-800"></div>
        <p class="text-[10px] font-black text-text-muted uppercase tracking-widest opacity-40">Menampilkan {{ count($exams) }} Ujian Aktif</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($exams as $exam)
        <div wire:key="{{ $exam['id'] }}" class="bg-bg-surface dark:bg-bg-surface rounded-[2.5rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main hover:border-primary/40 transition-all flex flex-col group">
            <div class="p-8 flex-1">
                <div class="flex justify-between items-start mb-8">
                    <div class="flex items-center gap-4">
                        <input type="checkbox" wire:model.live="selectedExams" value="{{ $exam['id'] }}" class="w-6 h-6 rounded-xl text-primary border-border-main dark:border-slate-700 focus:ring-primary/20 bg-gray-50/50 dark:bg-slate-900">
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-inner
                        @if($exam['status'] == 'completed') bg-gray-100 text-gray-500
                        @elseif($exam['status'] == 'ongoing') bg-green-500/10 text-green-600 animate-pulse border border-green-500/20
                        @else bg-primary/10 text-primary border border-primary/20 @endif">
                        @if($exam['status'] == 'completed') SELESAI
                        @elseif($exam['status'] == 'ongoing') BERJALAN
                        @else TERJADWAL @endif
                        </span>
                    </div>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="p-2 text-text-muted hover:text-text-main transition-colors opacity-40 group-hover:opacity-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                        </button>
                        <div x-show="open" class="absolute right-0 mt-4 w-56 bg-bg-surface dark:bg-slate-900 rounded-3xl shadow-2xl py-3 z-30 border border-border-main dark:border-slate-800 ring-1 ring-black/5">
                            <a href="{{ route('teacher.exams.edit', $exam['id']) }}" class="flex items-center gap-3 px-6 py-3 text-[10px] font-black uppercase tracking-widest text-text-main hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">Edit Ujian</a>
                            <button type="button" wire:click="duplicateExam({{ $exam['id'] }})" class="w-full flex items-center gap-3 px-6 py-3 text-[10px] font-black uppercase tracking-widest text-text-main hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">Duplikat Ujian</button>
                            <div class="h-px bg-border-subtle dark:bg-slate-800 my-2"></div>
                            <button type="button" wire:click="openDeleteModal({{ $exam['id'] }})" @click="open = false" class="w-full flex items-center gap-3 px-6 py-3 text-[10px] font-black uppercase tracking-widest text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">Hapus Ujian</button>
                        </div>
                    </div>
                </div>

                <h3 class="font-black text-2xl text-text-main mb-2 tracking-tight group-hover:text-primary transition-colors italic leading-tight">{{ $exam['name'] }}</h3>
                <div class="flex items-center gap-3 mb-8">
                    <span class="text-[10px] font-black text-primary uppercase tracking-widest">{{ $exam['subject'] }}</span>
                    <span class="w-1 h-1 rounded-full bg-border-main"></span>
                    <span class="text-[10px] font-black text-text-muted uppercase tracking-widest">{{ $exam['class'] }}</span>
                </div>

                <div class="space-y-4 pt-6 border-t border-border-subtle dark:border-slate-800/50">
                    <div class="flex items-center text-[10px] font-black text-text-muted uppercase tracking-[0.2em] opacity-60">
                        <svg class="w-4 h-4 mr-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ date('d M Y', strtotime($exam['date'])) }} 
                        <span class="mx-3 opacity-20">|</span> 
                        {{ $exam['start_time'] ?? '08:00' }} - {{ $exam['end_time'] ?? '09:30' }}
                    </div>
                    <div class="flex items-center text-[10px] font-black text-text-muted uppercase tracking-[0.2em] opacity-60">
                        <svg class="w-4 h-4 mr-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $exam['duration'] }} Menit
                    </div>
                    <div class="flex items-center text-[10px] font-black text-text-muted uppercase tracking-[0.2em] opacity-60">
                        <svg class="w-4 h-4 mr-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        {{ $exam['questions_count'] }} Butir Soal
                    </div>
                </div>
            </div>

            <div class="px-8 py-6 bg-gray-50/50 dark:bg-slate-800/30 border-t border-border-subtle dark:border-slate-800 grid grid-cols-2 gap-4 mt-auto">
                @if($exam['status'] == 'ongoing')
                    <a href="{{ route('teacher.monitoring.detail', $exam['id']) }}" class="col-span-2 flex justify-center items-center gap-3 bg-primary text-white text-[10px] font-black uppercase tracking-[0.2em] py-4 rounded-2xl shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-100 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        Monitoring Live
                    </a>
                @elseif($exam['status'] == 'completed')
                    <a href="{{ route('teacher.grading.index') }}" class="flex justify-center items-center gap-2 bg-bg-surface dark:bg-slate-800 border border-border-main dark:border-slate-700 text-text-main text-[10px] font-black uppercase tracking-widest py-3 rounded-xl hover:bg-gray-100 transition-all">
                        Analisis Nilai
                    </a>
                    <a href="{{ route('teacher.reports.index') }}" class="flex justify-center items-center gap-2 bg-bg-surface dark:bg-slate-800 border border-border-main dark:border-slate-700 text-text-main text-[10px] font-black uppercase tracking-widest py-3 rounded-xl hover:bg-gray-100 transition-all">
                        Laporan Ujian
                    </a>
                @else
                    <a href="{{ route('teacher.exams.edit', $exam['id']) }}" class="col-span-2 flex justify-center items-center gap-3 bg-bg-surface dark:bg-slate-800 border border-border-main dark:border-slate-700 text-text-main text-[10px] font-black uppercase tracking-[0.2em] py-4 rounded-2xl hover:bg-gray-100 transition-all shadow-sm">
                        <svg class="w-4 h-4 opacity-40 transition-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit Ujian
                    </a>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <x-empty-state 
                title="Agenda Ujian Kosong" 
                message="Mulai dengan membuat ujian baru untuk kelas Anda! 🚀" 
                icon="folder-open" 
            />
        </div>
        @endforelse
    </div>

    <!-- Bulk Action Floating Bar -->
    @if(count($selectedExams) > 0)
    <div class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-slate-900 dark:bg-slate-800 px-10 py-5 rounded-[2rem] shadow-2xl border border-white/10 flex items-center gap-10 z-40 animate-bounce-in ring-4 ring-primary/20">
        <div class="flex items-center gap-4">
            <span class="bg-primary text-white text-xs font-black px-3 py-1.5 rounded-xl shadow-lg">{{ count($selectedExams) }}</span>
            <span class="text-xs font-black text-white uppercase tracking-widest opacity-80">Mission Threads</span>
        </div>
        <div class="h-8 w-px bg-white/10"></div>
        <button wire:click="openBulkDeleteModal" class="group flex items-center gap-3 text-xs font-black text-white px-4 py-2 rounded-xl hover:bg-red-500/10 hover:text-red-500 transition-all uppercase tracking-widest">
            <div class="p-2 rounded-lg bg-white/5 border border-white/5 group-hover:border-red-500/30 text-white/50 group-hover:text-red-500 transition-all shadow-inner">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            Purge
        </button>
    </div>
    @endif

    <!-- Beautiful Bulk Delete Modal -->
    <div 
        x-data="{ show: @entangle('showBulkDeleteModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 px-4 min-h-screen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="show = false"></div>

        <!-- Modal Panel -->
        <div class="relative bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden transform transition-all ring-1 ring-black/5"
             x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-red-50 dark:bg-red-500/10 text-red-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-red-500/10 ring-1 ring-red-500/20">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-text-main mb-3 leading-tight">Hapus {{ count($selectedExams) }} Ujian?</h3>
                <p class="text-text-muted text-sm leading-relaxed px-4">
                    Anda akan menghapus <span class="font-bold text-red-500 mx-1">{{ count($selectedExams) }}</span> ujian yang dipilih. <br>
                    Tindakan ini <span class="font-bold text-text-main">tidak dapat dibatalkan</span> dan semua data siswa terkait akan hilang.
                </p>
            </div>
            
            <div class="p-6 bg-gray-50/50 dark:bg-slate-800/50 border-t border-border-subtle dark:border-slate-800 flex justify-center gap-4">
                <button @click="show = false" class="px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest text-text-muted hover:bg-gray-200/50 dark:hover:bg-slate-800 transition-colors">
                    Batalkan
                </button>
                <button wire:click="bulkDelete" class="px-8 py-3 rounded-xl bg-red-500 hover:bg-red-600 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-red-500/20 transition-all hover:scale-105 active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    Ya, Hapus Semua
                </button>
            </div>
        </div>
    </div>

    <!-- Beautiful Individual Delete Modal -->
    <div 
        x-data="{ show: @entangle('showDeleteModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 px-4 min-h-screen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="show = false"></div>

        <!-- Modal Panel -->
        <div class="relative bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden transform transition-all ring-1 ring-black/5"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-red-50 dark:bg-red-500/10 text-red-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-red-500/10 ring-1 ring-red-500/20">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-text-main mb-3 leading-tight">Hapus Ujian Ini?</h3>
                <p class="text-text-muted text-sm leading-relaxed px-4">
                    Apakah Anda yakin ingin menghapus ujian ini? <br>
                    Tindakan ini <span class="font-bold text-text-main">tidak dapat dibatalkan</span>.
                </p>
            </div>
            
            <div class="p-6 bg-gray-50/50 dark:bg-slate-800/50 border-t border-border-subtle dark:border-slate-800 flex justify-center gap-4">
                <button @click="show = false" class="px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest text-text-muted hover:bg-gray-200/50 dark:hover:bg-slate-800 transition-colors">
                    Batalkan
                </button>
                <button wire:click="deleteExam" class="px-8 py-3 rounded-xl bg-red-500 hover:bg-red-600 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-red-500/20 transition-all hover:scale-105 active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>


</div>

