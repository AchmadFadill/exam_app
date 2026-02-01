@section('title', 'Kelola Ujian')

<div class="space-y-6">
    <x-header 
        title="Daftar Ujian" 
        subtitle="Kelola Ujian Anda Disini"
    >
        <x-button href="{{ route('teacher.exams.create') }}" variant="primary" class="group px-8 py-3.5 rounded-[2rem] text-sm tracking-widest">
            <svg class="w-5 h-5 transition-transform mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            BUAT UJIAN BARU 
        </x-button>
    </x-header>

    <!-- Exam Filter/Select All -->
    <div class="flex items-center gap-4 mb-8">
        <div class="flex items-center gap-3 px-6 py-3 bg-bg-surface dark:bg-bg-surface border border-border-main dark:border-border-main rounded-2xl shadow-sm">
            <input type="checkbox" wire:model.live="selectAll" id="selectAllExams" class="w-5 h-5 rounded-lg text-primary border-border-main dark:border-slate-700 focus:ring-primary/20 bg-transparent">
            <label for="selectAllExams" class="text-[10px] font-black text-text-muted uppercase tracking-[0.2em] cursor-pointer">Pilih Semua</label>
        </div>
        <div class="h-6 w-px bg-border-subtle dark:bg-slate-800"></div>
        <p class="text-[10px] font-black text-text-muted uppercase tracking-widest ">Menampilkan {{ count($exams) }} Jadwal Ujian</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($exams as $exam)
        <div class="bg-bg-surface dark:bg-bg-surface rounded-[2.5rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main overflow-hidden hover:border-primary/40 transition-all flex flex-col group">
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
                            @else AKAN DATANG @endif
                        </span>
                    </div>
                    <div class="relative" x-data="{ open: false }">
                        <x-button @click="open = !open" @click.away="open = false" variant="secondary" size="sm" square="true" class="!bg-transparent !border-transparent !shadow-none text-text-muted hover:text-text-main opacity-40 group-hover:opacity-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                        </x-button>
                        <div x-show="open" class="absolute right-0 mt-4 w-56 bg-bg-surface dark:bg-slate-900 rounded-3xl shadow-2xl py-3 z-20 border border-border-main dark:border-slate-800 ring-1 ring-black/5 overflow-hidden">
                            <a href="#" class="flex items-center gap-3 px-6 py-3 text-[10px] font-black uppercase tracking-widest text-text-main hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">Edit ujian</a>
                            <a href="#" class="flex items-center gap-3 px-6 py-3 text-[10px] font-black uppercase tracking-widest text-text-main hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">Duplikat Ujian</a>
                            <div class="h-px bg-border-subtle dark:bg-slate-800 my-2"></div>
                            <a href="#" class="flex items-center gap-3 px-6 py-3 text-[10px] font-black uppercase tracking-widest text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">Hapus</a>
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
                    <div class="flex items-center text-[10px] font-black uppercase tracking-[0.2em]">
                        <svg class="w-4 h-4 mr-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ date('d M Y', strtotime($exam['date'])) }}
                        <span class="mx-3 opacity-20">|</span>
                        {{ $exam['start_time'] ?? '08:00' }} - {{ $exam['end_time'] ?? '09:30' }}
                    </div>
                    <div class="flex items-center text-[10px] font-black uppercase tracking-[0.2em]">
                        <svg class="w-4 h-4 mr-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $exam['duration'] }} Menit 
                    </div>
                    <div class="flex items-center text-[10px] font-black uppercase tracking-[0.2em]">
                        <svg class="w-4 h-4 mr-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        {{ $exam['questions_count'] }} Soal
                    </div>
                </div>
            </div>

            <div class="px-8 py-6 bg-gray-50/50 dark:bg-slate-800/30 border-t border-border-subtle dark:border-slate-800 grid grid-cols-2 gap-4 mt-auto">
                @if($exam['status'] == 'ongoing')
                    <x-button href="{{ route('admin.monitor.detail', $exam['id']) }}" variant="primary" class="col-span-2 py-4 text-[10px] tracking-[0.2em]">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        MONITOR UJIAN 
                    </x-button>
                @elseif($exam['status'] == 'completed')
                    <x-button href="#" variant="soft" class="text-[10px] py-3 tracking-widest">
                        Lihat Hasil
                    </x-button>
                    <x-button href="#" variant="soft" class="text-[10px] py-3 tracking-widest">
                        Daftar Nilai
                    </x-button>
                @else
                    <x-button href="#" variant="soft" class="col-span-2 py-4 text-[10px] tracking-[0.2em]">
                        <svg class="w-4 h-4 mr-3 opacity-40 transition-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Atur Jadwal
                    </x-button>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Bulk Action Floating Bar -->
    @if(count($selectedExams) > 0)
    <div class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-slate-900 dark:bg-slate-800 px-10 py-5 rounded-[2rem] shadow-2xl border border-white/10 flex items-center gap-10 z-40 animate-bounce-in ring-4 ring-primary/20">
        <div class="flex items-center gap-4">
            <span class="bg-primary text-white text-xs font-black px-3 py-1.5 rounded-xl shadow-lg">{{ count($selectedExams) }}</span>
            <span class="text-xs font-black text-white uppercase tracking-widest opacity-80">Sesi Terpilih</span>
        </div>
        <div class="h-8 w-px bg-white/10"></div>
        <x-button wire:click="openBulkDeleteModal" variant="secondary" class="!bg-transparent !border-transparent hover:!bg-red-500/10 text-white hover:text-red-500 gap-3 font-black uppercase tracking-widest text-xs">
            <div class="p-2 rounded-lg bg-white/5 border border-white/5 group-hover:border-red-500/30 text-white/50 group-hover:text-red-500 transition-all shadow-inner">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            Hapus Jadwal ujian
        </x-button>
    </div>
    @endif

    <!-- Bulk Delete Modal -->
    @if($showBulkDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showBulkDeleteModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden transform transition-all">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-text-main mb-2">Hapus Ujian Massal?</h3>
                <p class="text-gray-500">Anda akan menghapus <span class="font-bold">{{ count($selectedExams) }}</span> ujian yang dipilih. Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="p-6 bg-gray-50 flex justify-center gap-3">
                <x-button variant="secondary" wire:click="$set('showBulkDeleteModal', false)">Batal</x-button>
                <x-button variant="danger" wire:click="bulkDelete">Ya, Hapus Semua</x-button>
            </div>
        </div>
    </div>
    @endif


</div>
