@section('title', 'Dashboard Siswa')


<div class="space-y-8">
    <!-- Hero Section -->
    <!-- Hero Section -->
    <x-page-header 
        title="Dashboard" 
        highlight="Siswa" 
        subtitle="Sudah siap untuk mengerjakan ujian hari ini?" 
    />

    <!-- Student Info Card -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-xl shadow-blue-900/5 border border-blue-50 dark:border-slate-800 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-primary/5 to-transparent rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
        
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-800 border border-blue-100 dark:border-slate-700 flex items-center justify-center shadow-inner">
                    <span class="text-2xl font-black text-primary">{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
                <div>
                    <h2 class="text-lg font-black text-text-main">{{ auth()->user()->name }}</h2>
                    <p class="text-xs font-medium text-text-muted">NIS: {{ auth()->user()->student->nis ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 md:gap-8 border-t md:border-t-0 md:border-l border-gray-100 dark:border-slate-800 pt-4 md:pt-0 md:pl-8">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-text-muted font-bold opacity-60 mb-1">Kelas</p>
                    <p class="text-sm font-black text-text-main">{{ auth()->user()->student->classroom->name ?? 'Belum ada kelas' }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-text-muted font-bold opacity-60 mb-1">Wali Kelas</p>
                    <p class="text-sm font-black text-text-main">{{ auth()->user()->student->classroom->teacher->user->name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Exams (Ujian Tersedia) - HERO FEATURE -->
    <section class="mb-16">
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-[10px] font-black text-text-muted uppercase tracking-[0.3em] flex items-center gap-4">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
                Ujian Tersedia ({{ count($active_exams) }})
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @forelse($active_exams as $exam)
            <div class="bg-bg-surface dark:bg-slate-900 rounded-[2.5rem] shadow-2xl shadow-black/5 border border-border-main dark:border-white/5 overflow-hidden hover:border-primary/40 transition-all relative group">
                 @if($exam['is_urgent'])
                <div class="absolute top-0 right-0 mt-6 mr-8">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[8px] font-black uppercase tracking-widest bg-red-500/10 text-red-600 animate-pulse border border-red-500/20">
                        SEGERA BERAKHIR
                    </span>
                </div>
                @endif
                
                <div class="p-10">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="px-3 py-1 bg-primary/10 text-primary text-[10px] font-black uppercase tracking-[0.2em] rounded-lg">
                                {{ $exam['subject'] }}
                            </span>
                            <h4 class="mt-6 text-3xl font-black text-text-main group-hover:text-primary transition-colors tracking-tight italic leading-tight">
                                {{ $exam['title'] }}
                            </h4>
                            <p class="text-text-muted text-[10px] font-bold uppercase tracking-widest mt-2 opacity-50">Guru Pengampu: {{ $exam['teacher'] }}</p>
                        </div>
                    </div>

                    <div class="mt-10 flex items-center gap-10 text-[10px] font-black text-text-muted uppercase tracking-widest opacity-60">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ $exam['duration'] }} Menit</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <span>{{ $exam['questions_count'] }} Soal</span>
                        </div>
                    </div>

                    <div class="mt-10 pt-10 border-t border-border-subtle dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black text-text-muted uppercase tracking-widest opacity-40">Cut-off Time</p>
                            <p class="font-black text-text-main uppercase tracking-widest">{{ $exam['end_time'] }} WIB</p>
                        </div>
                        <a href="{{ route('student.exam.start', $exam['id']) }}" class="group/btn inline-flex items-center px-8 py-4 bg-primary hover:bg-blue-700 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl shadow-xl shadow-primary/20 transition-all hover:scale-[1.02] active:scale-100">
                            Kerjakan Sekarang
                            <svg class="ml-3 w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </a>
                    </div>
                </div>
                <div class="h-2 w-full bg-gray-100 dark:bg-slate-800">
                    <div class="h-2 bg-primary rounded-r-full shadow-lg shadow-primary/20" style="width: 45%"></div>
                </div>
            </div>
            @empty
            <div class="col-span-full">
                <x-empty-state 
                    title="Tidak Ada Ujian Aktif" 
                    message="Belum ada ujian yang dapat Anda kerjakan saat ini." 
                    icon="coffee" 
                />
            </div>
            @endforelse
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <h3 class="text-[10px] font-black text-text-muted uppercase tracking-[0.3em]">Jadwal Mendatang</h3>
            <div class="bg-bg-surface dark:bg-slate-900 rounded-[2.5rem] shadow-2xl shadow-black/5 border border-white/5 divide-y divide-border-subtle dark:divide-slate-800 overflow-hidden">
                @forelse($upcoming_exams as $exam)
                <div class="p-8 flex items-center gap-6 hover:bg-gray-50/50 dark:hover:bg-slate-800/20 transition-all group">
                    <div class="shrink-0 w-20 h-20 bg-gray-100/50 dark:bg-slate-800 rounded-[1.5rem] flex flex-col items-center justify-center border border-border-main dark:border-slate-700 shadow-inner transition-transform group-hover:scale-105">
                         <svg class="w-8 h-8 text-text-muted opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-[8px] font-black text-primary bg-primary/10 px-3 py-1 rounded-full uppercase tracking-widest">{{ $exam['subject'] }}</span>
                        </div>
                        <h4 class="text-xl font-black text-text-main truncate tracking-tight uppercase group-hover:text-primary transition-colors italic leading-tight">{{ $exam['title'] }}</h4>
                        <p class="text-[10px] font-bold text-text-muted mt-2 uppercase tracking-widest opacity-40">{{ date('d M Y', strtotime($exam['date'])) }}</p>
                    </div>
                     <div class="text-right sr-only sm:not-sr-only">
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[8px] font-black uppercase tracking-widest bg-gray-100 dark:bg-slate-800 text-text-muted opacity-60">
                            TERJADWAL
                        </span>
                    </div>
                </div>
                @empty
                 <div class="p-10">
                    <x-empty-state 
                        title="Jadwal Kosong" 
                        message="Tidak ada jadwal ujian mendatang dalam waktu dekat." 
                        icon="folder-open" 
                    />
                </div>
                @endforelse
            </div>
        </div>

        <!-- Student Stats -->
        <div class="space-y-8">
            <h3 class="text-[10px] font-black text-text-muted uppercase tracking-[0.3em]">Statistik Belajar</h3>
            
            <div class="bg-gradient-to-br from-primary via-blue-700 to-indigo-900 rounded-[2rem] shadow-2xl p-8 text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-blue-100/60 text-[10px] font-black uppercase tracking-widest">Rata-rata Nilai</p>
                    <h2 class="text-6xl font-black mt-2 tracking-tighter">{{ $stats['avg_score'] }}</h2>
                    <div class="mt-10 flex items-center gap-3 text-[10px] font-black uppercase tracking-widest text-primary bg-white px-5 py-3 rounded-2xl w-fit shadow-xl transition-transform group-hover:scale-105 active:scale-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        <span>Capaian: Top 10%</span>
                    </div>
                </div>
                <div class="absolute right-0 top-0 w-48 h-48 bg-white rounded-full opacity-5 blur-[80px] -mr-16 -mt-16 group-hover:opacity-10 transition-opacity"></div>
            </div>

        </div>
    </div>
</div>
