@section('title', 'Kelola Ujian')

<div class="space-y-6">
    <div class="mb-12 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6">
    <x-header 
        title="Daftar Ujian" 
        subtitle="Manajemen Pelaksanaan Ujian" 
    />
        <div>
            <x-button href="{{ route('teacher.exams.create') }}" variant="primary" class="group px-8 py-3.5 rounded-[2rem] text-sm tracking-widest">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                BUAT UJIAN BARU
            </x-button>
        </div>
    </div>

    <!-- Exam Filter/Select All -->
    <div class="flex flex-wrap items-center gap-3 sm:gap-4 mb-6 sm:mb-8">
        <div class="flex items-center gap-2 sm:gap-3 px-4 sm:px-6 py-2.5 sm:py-3 bg-bg-surface dark:bg-slate-800 border border-border-main dark:border-slate-700 rounded-xl sm:rounded-2xl shadow-sm">
            <input type="checkbox" wire:model.live="selectAll" id="selectAllExams" class="w-4 h-4 sm:w-5 sm:h-5 rounded text-primary border-border-main dark:border-slate-600 focus:ring-primary/20 bg-bg-surface dark:bg-slate-900">
            <label for="selectAllExams" class="text-[9px] sm:text-[10px] font-black text-text-muted uppercase tracking-widest cursor-pointer">Pilih Semua</label>
        </div>
        <div class="hidden sm:block h-6 w-px bg-border-subtle dark:bg-slate-800"></div>
        <p class="text-[9px] sm:text-[10px] font-black text-text-muted uppercase tracking-widest opacity-40">Showing {{ $exams->count() }} of {{ $exams->total() }} Exams</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($exams as $exam)
        <x-exam-card 
            wire:key="{{ $exam['id'] }}"
            :exam="$exam"
            :edit-route="route('teacher.exams.edit', $exam['id'])"
            duplicate-action="duplicateExam"
            delete-action="openDeleteModal"
        >
            <x-slot name="footer">
                @if($exam['status'] == 'ongoing')
                    <x-button href="{{ route('teacher.monitoring.detail', $exam['id']) }}" variant="primary" class="col-span-2 py-4 text-[10px] tracking-[0.2em]">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        MONITORING LIVE
                    </x-button>
                @elseif($exam['status'] == 'completed')
                    <x-button href="{{ route('teacher.grading.index') }}" variant="soft" class="text-[10px] py-3 tracking-widest">
                        Beri Nilai
                    </x-button>
                    <x-button href="{{ route('teacher.reports.index') }}" variant="soft" class="text-[10px] py-3 tracking-widest">
                        Laporan Ujian
                    </x-button>
                @else
                    <x-button href="{{ route('teacher.exams.edit', $exam['id']) }}" variant="secondary" class="col-span-2 py-4 text-[10px] tracking-[0.2em]">
                        <svg class="w-4 h-4 mr-3 opacity-40 transition-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        EDIT UJIAN
                    </x-button>
                @endif
            </x-slot>
        </x-exam-card>
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

    @if($exams->hasPages())
    <div class="mt-8">
        {{ $exams->links() }}
    </div>
    @endif

    <!-- Bulk Action Floating Bar -->
    @if(count($selectedExams) > 0)
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = true, 100)" class="fixed bottom-12 left-1/2 -translate-x-1/2 z-50">
        <div class="bg-gray-900/90 backdrop-blur-xl border border-white/10 px-8 py-5 rounded-[2.5rem] shadow-2xl flex items-center gap-8 ring-1 ring-white/5 animate-in fade-in slide-in-from-bottom-8 duration-500">
            <div class="flex items-center gap-4 border-r border-white/10 pr-8">
                <div class="w-10 h-10 bg-primary/20 rounded-2xl flex items-center justify-center">
                    <span class="text-primary font-black text-lg">{{ count($selectedExams) }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-white font-black text-xs uppercase tracking-widest italic">Ujian Terpilih</span>
                    <span class="text-white/40 text-[10px] font-bold uppercase tracking-widest">Aksi Massal Tersedia</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <x-button wire:click="openBulkDeleteModal" variant="secondary" class="!bg-transparent !border-transparent hover:!bg-red-500/10 text-white hover:text-red-500 gap-3 font-black uppercase tracking-widest text-xs">
                    <svg class="w-5 h-5 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Hapus Terpilih
                </x-button>
                <button @click="$wire.set('selectedExams', [])" class="px-6 py-3 text-white/40 hover:text-white text-xs font-black uppercase tracking-widest transition-colors hover:bg-white/5 rounded-2xl">
                    Batalkan
                </button>
            </div>
        </div>
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
