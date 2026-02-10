@section('title', 'Dashboard Monitoring')

<div class="space-y-6">
    <x-header 
        title="Dashboard Monitoring" 
        subtitle="{{ request()->is('admin/*') ? 'Pantau semua ujian yang sedang berlangsung' : 'Pantau ujian siswa Anda yang sedang berlangsung' }}" 
    />

    <!-- Active Exams Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($activeExams as $exam)
        <div class="bg-white rounded-2xl shadow-sm border border-border-main overflow-hidden hover:shadow-lg transition-all duration-300 relative group flex flex-col">
            <!-- Header -->
            <div class="p-5 sm:p-6 border-b border-gray-50 bg-gradient-to-r from-white to-blue-50/20">
                <div class="flex justify-between items-start mb-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] sm:text-xs font-bold bg-green-100 text-green-700 animate-pulse border border-green-200">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                        LIVE
                    </span>
                    <span class="text-[10px] sm:text-xs font-black text-gray-400 font-mono tracking-tighter">{{ $exam['start_time'] }} - {{ $exam['end_time'] }}</span>
                </div>
                <h3 class="font-black text-base sm:text-lg text-text-main leading-tight mb-1 group-hover:text-primary transition-colors truncate uppercase tracking-tight italic">{{ $exam['name'] }}</h3>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <p class="text-[10px] sm:text-xs font-bold text-primary truncate max-w-[100px] sm:max-w-none">{{ $exam['subject'] }}</p>
                    <span class="text-gray-300">•</span>
                    <p class="text-[10px] sm:text-xs font-bold text-text-muted truncate max-w-[100px] sm:max-w-none uppercase tracking-widest italic">{{ $exam['class'] }}</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="p-5 sm:p-6 grid grid-cols-3 gap-3 sm:gap-4 text-center bg-gray-50/30 flex-1">
                <div class="flex flex-col items-center justify-center">
                    <div class="text-lg sm:text-2xl font-black text-text-main">{{ $exam['total_students'] }}</div>
                    <div class="text-[8px] sm:text-[9px] uppercase tracking-widest text-text-muted font-black mt-1 leading-tight">Total<br class="sm:hidden"> Siswa</div>
                </div>
                <div class="flex flex-col items-center justify-center border-x border-gray-100 px-2 sm:px-4">
                    <div class="text-lg sm:text-2xl font-black text-primary">{{ $exam['working'] }}</div>
                    <div class="text-[8px] sm:text-[9px] uppercase tracking-widest text-text-muted font-black mt-1 leading-tight">Mengerja-<br class="sm:hidden">kan</div>
                </div>
                <div class="flex flex-col items-center justify-center">
                    <div class="text-lg sm:text-2xl font-black text-green-600">{{ $exam['finished'] }}</div>
                    <div class="text-[8px] sm:text-[9px] uppercase tracking-widest text-text-muted font-black mt-1 leading-tight">Sudah<br class="sm:hidden"> Selesai</div>
                </div>
            </div>

            <!-- Action -->
            <div class="p-4 sm:p-5 bg-white border-t border-gray-100 mt-auto">
                    <x-button href="{{ route($detailRoute, $exam['id']) }}" variant="primary" class="w-full py-3.5 sm:py-4 text-[10px] font-black uppercase tracking-[0.2em]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        MONITORING LIVE 
                    </x-button>
            </div>
        </div>
        @endforeach
        
        @if(count($activeExams) === 0)
        <div class="col-span-full">
            <x-empty-state 
                title="Tidak ada Ujian Berlangsung" 
                message="Tidak ada ujian yang sedang berlangsung saat ini" 
                icon="coffee" 
            />
        </div>
        @endif
    </div>
</div>
