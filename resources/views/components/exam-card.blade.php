@props([
    'exam',
    'editRoute' => null,
    'duplicateAction' => null,
    'deleteAction' => null,
    'checkboxModel' => 'selectedExams',
])

<div {{ $attributes->merge(['class' => 'bg-bg-surface dark:bg-bg-surface rounded-[2rem] sm:rounded-[2.5rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main overflow-hidden hover:border-primary/40 transition-all flex flex-col group']) }}>
    <div class="p-6 sm:p-8 flex-1">
        <div class="flex justify-between items-start mb-6 sm:mb-8">
            <div class="flex items-center gap-3 sm:gap-4">
                <input type="checkbox" wire:model.live="{{ $checkboxModel }}" value="{{ $exam['id'] }}" class="w-5 h-5 sm:w-6 h-6 rounded-lg sm:rounded-xl text-primary border-border-main dark:border-slate-700 focus:ring-primary/20 bg-gray-50/50 dark:bg-slate-900">
                <span class="inline-flex items-center px-3 sm:px-4 py-1 sm:py-1.5 rounded-full text-[9px] sm:text-[10px] font-black uppercase tracking-widest shadow-inner
                    @if($exam['status'] == 'completed') bg-gray-100 text-gray-500
                    @elseif($exam['status'] == 'ongoing') bg-green-500/10 text-green-600 animate-pulse border border-green-500/20
                    @else bg-primary/10 text-primary border border-primary/20 @endif">
                    @if($exam['status'] == 'completed') SELESAI
                    @elseif($exam['status'] == 'ongoing') BERJALAN
                    @else TERJADWAL @endif
                </span>
            </div>
            
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="p-1 sm:p-2 text-text-muted hover:text-text-main transition-colors opacity-40 group-hover:opacity-100">
                    <svg class="w-5 h-5 sm:w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                </button>
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 sm:mt-4 w-48 sm:w-56 bg-bg-surface dark:bg-slate-900 rounded-2xl sm:rounded-3xl shadow-2xl py-2 sm:py-3 z-30 border border-border-main dark:border-slate-800 ring-1 ring-black/5 overflow-hidden" 
                     style="display: none;">
                    
                    @if($editRoute)
                        <a href="{{ $editRoute }}" class="flex items-center gap-3 px-5 sm:px-6 py-2.5 sm:py-3 text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-text-main hover:bg-gray-50 dark:hover:bg-slate-800 transition-all hover:scale-[1.02] active:scale-[0.98]">Edit Ujian</a>
                    @endif

                    @if($duplicateAction)
                        <button type="button" wire:click="{{ $duplicateAction }}({{ $exam['id'] }})" @click="open = false" class="w-full flex items-center gap-3 px-5 sm:px-6 py-2.5 sm:py-3 text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-text-main hover:bg-gray-50 dark:hover:bg-slate-800 transition-all hover:scale-[1.02] active:scale-[0.98] text-left">Duplikat Ujian</button>
                    @endif

                    @if($deleteAction)
                        <div class="h-px bg-border-subtle dark:bg-slate-800 my-1.5 sm:my-2"></div>
                        <button type="button" wire:click="{{ $deleteAction }}({{ $exam['id'] }})" @click="open = false" class="w-full flex items-center gap-3 px-5 sm:px-6 py-2.5 sm:py-3 text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all hover:scale-[1.02] active:scale-[0.98] text-left">Hapus Ujian</button>
                    @endif
                </div>
            </div>
        </div>

        <h3 class="font-black text-xl sm:text-2xl text-text-main mb-1.5 sm:mb-2 tracking-tight group-hover:text-primary transition-colors italic leading-tight uppercase">{{ $exam['name'] }}</h3>
        <div class="flex items-center gap-2 sm:gap-3 mb-6 sm:mb-8">
            <span class="text-[9px] sm:text-[10px] font-black text-primary uppercase tracking-widest">{{ $exam['subject'] }}</span>
            <span class="w-1 h-1 rounded-full bg-border-main"></span>
            <span class="text-[9px] sm:text-[10px] font-black text-text-muted uppercase tracking-widest truncate max-w-[150px]">{{ $exam['class'] }}</span>
        </div>

        <div class="space-y-3 sm:space-y-4 pt-5 sm:pt-6 border-t border-border-subtle dark:border-slate-800/50">
            <div class="flex items-center text-[9px] sm:text-[10px] font-black text-text-muted uppercase tracking-[0.2em] opacity-60">
                <svg class="w-3.5 h-3.5 sm:w-4 h-4 mr-2 sm:mr-3 opacity-40 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                {{ date('d M Y', strtotime($exam['date'])) }} 
                <span class="mx-2 sm:mx-3 opacity-20">|</span> 
                {{ $exam['start_time'] ?? '08:00' }} - {{ $exam['end_time'] ?? '09:30' }}
            </div>
            <div class="flex items-center text-[9px] sm:text-[10px] font-black text-text-muted uppercase tracking-[0.2em] opacity-60">
                <svg class="w-3.5 h-3.5 sm:w-4 h-4 mr-2 sm:mr-3 opacity-40 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ $exam['duration'] }} Menit
            </div>
            <div class="flex items-center text-[9px] sm:text-[10px] font-black text-text-muted uppercase tracking-[0.2em] opacity-60">
                <svg class="w-3.5 h-3.5 sm:w-4 h-4 mr-2 sm:mr-3 opacity-40 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                {{ $exam['questions_count'] }} Butir Soal
            </div>
        </div>
    </div>

    @if(isset($footer))
        <div class="px-6 sm:px-8 py-4 sm:py-6 bg-gray-50/50 dark:bg-slate-800/30 border-t border-border-subtle dark:border-slate-800 grid grid-cols-2 gap-3 sm:gap-4 mt-auto">
            {{ $footer }}
        </div>
    @endif
</div>
