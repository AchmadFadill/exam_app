@section('title', 'Dashboard')

<div class="space-y-8">
    <x-page-header 
        title="Teacher" 
        highlight="Dashboard" 
        subtitle="Ringkasan aktivitas dan kendali ujian hari ini" 
    />

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Stat Card 1: Active Exams (Red/Animated) -->
        <x-card variant="stat" title="Ujian Berlangsung" :value="$stats['active_exams']" subtitle="Sedang aktif saat ini" color="red">
             <x-slot name="icon">
                <div class="relative">
                    <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    </span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
             </x-slot>
        </x-card>

        <!-- Stat Card 2: Grading Needed (Amber) -->
        <x-card variant="stat" title="Perlu Dikoreksi" :value="$stats['grading_needed']" subtitle="Menunggu penilaian" color="amber">
             <x-slot name="icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
             </x-slot>
        </x-card>

        <!-- Stat Card 3: Completed Exams (Green) -->
        <x-card variant="stat" title="Ujian Selesai" :value="$stats['completed_exams']" subtitle="Total dilaksanakan" color="green">
             <x-slot name="icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
             </x-slot>
        </x-card>

        <!-- Stat Card 4: Total Questions (Blue) -->
        <x-card variant="stat" title="Bank Soal" :value="$stats['questions_count']" subtitle="Total koleksi soal" color="primary">
             <x-slot name="icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
             </x-slot>
        </x-card>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Center Column (Live Activity) -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Live Monitoring Section -->
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        Live Activity ({{ count($ongoing_exams) }})
                    </h3>
                    <x-button href="{{ route('teacher.monitoring') }}" variant="soft">
                        SELENGKAPNYA
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </x-button>
                </div>
                
                <div class="bg-bg-surface dark:bg-bg-surface rounded-[2rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main divide-y divide-border-subtle dark:divide-border-subtle overflow-hidden">
                    @forelse($ongoing_exams as $exam)
                    <div class="p-8 hover:bg-gray-50/50 dark:hover:bg-slate-800/30 transition-all group">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                            <!-- Exam Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-[0.2em] bg-gray-100 dark:bg-slate-800 text-text-muted">{{ $exam['class'] }}</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-border-main dark:bg-slate-700"></span>
                                    <span class="text-xs font-black text-primary uppercase tracking-widest">{{ $exam['subject'] }}</span>
                                </div>
                                <h4 class="font-black text-text-main group-hover:text-primary transition-colors text-2xl tracking-tight">{{ $exam['name'] }}</h4>
                                <div class="flex items-center gap-6 mt-4 text-sm text-text-muted font-bold">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-5 h-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ $exam['start_time'] }} - {{ $exam['end_time'] }}
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <svg class="w-5 h-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        {{ $exam['finished_students'] }}/{{ $exam['total_students'] }} <span class="uppercase tracking-widest text-[10px] opacity-60">Finished</span>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Progress Circle / Action -->
                            <div class="flex items-center gap-5">
                                <div class="text-right hidden sm:block">
                                    <div class="text-3xl font-black text-text-main tracking-tighter">{{ $exam['percentage'] }}%</div>
                                    <div class="text-[10px] text-text-muted font-black uppercase tracking-[0.2em] opacity-60">Progress</div>
                                </div>
                                <div class="w-px h-12 bg-border-subtle dark:bg-slate-800 hidden sm:block"></div>
                                <a href="{{ route('teacher.exams.edit', $exam['id']) }}" class="px-6 py-3 bg-bg-surface dark:bg-slate-800 border border-border-main dark:border-border-main text-text-main font-black rounded-2xl hover:bg-gray-50 dark:hover:bg-slate-700 transition-all text-xs uppercase tracking-widest shadow-sm">
                                    Detail
                                </a>
                                <a href="{{ route('teacher.monitoring.detail', $exam['id']) }}" class="px-6 py-3 bg-primary text-white font-black rounded-2xl hover:bg-blue-700 transition-all text-xs uppercase tracking-widest shadow-xl shadow-primary/20 flex items-center gap-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Pantau
                                </a>
                            </div>
                        </div>
                        <!-- Progress Bar -->
                        <div class="mt-8 w-full bg-gray-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden shadow-inner">
                            <div class="bg-primary h-full rounded-full transition-all duration-1000 ease-out shadow-[0_0_12px_rgba(30,64,175,0.4)]" style="width: {{ $exam['percentage'] }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="p-16 text-center text-text-muted font-bold italic">
                        <p>Tidak ada aktivitas ujian yang terdeteksi.</p>
                    </div>
                    @endforelse
                </div>
            </section>

            <!-- Quick Actions Grid -->
             <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <a href="{{ route('teacher.questions') }}" class="group p-8 bg-gradient-to-br from-indigo-600 to-primary rounded-[2rem] shadow-xl shadow-primary/20 text-white relative overflow-hidden transition-all hover:scale-[1.02] active:scale-[1]">
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mb-6 backdrop-blur-sm group-hover:rotate-6 transition-transform">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <h4 class="font-black text-2xl tracking-tight italic">Buat <span class="not-italic">Ujian</span></h4>
                        <p class="text-indigo-100 text-sm mt-2 font-bold opacity-80 uppercase tracking-widest">Bank Soal</p>
                    </div>
                    <div class="absolute right-0 bottom-0 opacity-10 group-hover:opacity-20 transition-opacity transform translate-x-1/4 translate-y-1/4 rotate-12 pointer-events-none">
                        <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                </a>

                <a href="{{ route('teacher.exams.index') }}" class="group p-8 bg-bg-surface dark:bg-bg-surface border border-border-main dark:border-border-main rounded-[2rem] shadow-xl shadow-black/5 hover:border-primary/30 transition-all hover:scale-[1.02] active:scale-[1] relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-amber-50 dark:bg-amber-500/10 text-amber-600 rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-6 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h4 class="font-black text-2xl text-text-main tracking-tight italic">Jadwal <span class="not-italic text-amber-600">Ujian</span></h4>
                        <p class="text-text-muted text-sm mt-2 font-bold uppercase tracking-widest opacity-60">Jadwal & Kelas</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Right Column (Schedule & History) -->
        <div class="space-y-8">
            <!-- Upcoming Schedule -->
            <section>
                <div class="flex items-center justify-between mb-4">
                     <h3 class="text-lg font-bold text-gray-900">Jadwal Mendatang</h3>
                     <x-button href="{{ route('teacher.exams.index') }}" variant="soft">
                         SELENGKAPNYA
                         <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                     </x-button>
                </div>
                <div class="bg-bg-surface dark:bg-bg-surface rounded-[2rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main p-4">
                    @forelse($upcoming_exams as $exam)
                    <div class="flex gap-5 p-5 hover:bg-gray-50/50 dark:hover:bg-slate-800/50 rounded-2xl transition-all group cursor-pointer border border-transparent hover:border-border-subtle">
                        <div class="flex-shrink-0 w-16 h-16 flex flex-col items-center justify-center bg-blue-50/50 dark:bg-primary/10 text-primary rounded-xl border border-primary/5 shadow-inner">
                            <span class="text-[10px] font-black uppercase tracking-widest opacity-60">{{ $exam['month'] }}</span>
                            <span class="text-xl font-black tracking-tighter">{{ $exam['day'] }}</span>
                        </div>
                        <div class="flex-1 min-w-0 py-1">
                            <h5 class="text-sm font-black text-text-main truncate group-hover:text-primary transition-colors uppercase tracking-tight">{{ $exam['name'] }}</h5>
                            <p class="text-xs text-text-muted mt-1 font-bold uppercase tracking-wider opacity-60">{{ $exam['class'] }} <span class="mx-1 opacity-20">•</span> {{ $exam['time'] }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-text-muted font-bold italic opacity-60">
                        <p class="text-sm">Belum ada jadwal ujian mendatang.</p>
                    </div>
                    @endforelse
                </div>
            </section>

            <!-- Recent Activity -->
            <section>
                <h3 class="text-lg font-bold text-gray-900 mb-4">Aktivitas Terkini</h3>
                <div class="bg-bg-surface dark:bg-bg-surface rounded-[2rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main p-8">
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @forelse($recent_activities as $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-border-subtle dark:bg-slate-800" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-4">
                                        <div>
                                            <span class="h-10 w-10 rounded-xl flex items-center justify-center ring-8 ring-bg-surface dark:ring-slate-900
                                                @if($activity['type'] == 'success') bg-green-100 text-green-600
                                                @elseif($activity['type'] == 'warning') bg-amber-100 text-amber-600
                                                @elseif($activity['type'] == 'info') bg-blue-100 text-blue-600
                                                @else bg-gray-100 text-gray-500 @endif shadow-sm">
                                                
                                                @if(($activity['icon'] ?? '') == 'check-circle')
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                @elseif(($activity['icon'] ?? '') == 'plus-circle')
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                                @else
                                                    <!-- Default Icon (e.g. Activity) -->
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex flex-col">
                                            <p class="text-sm text-text-main font-black tracking-tight">{{ $activity['action'] }}</p>
                                            <span class="text-[10px] text-text-muted mt-1 font-black uppercase tracking-widest opacity-60">{{ $activity['time'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="py-4 text-center">
                                <p class="text-sm text-text-muted font-bold italic opacity-60">Belum ada aktivitas terkini.</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

            </section>
        </div>
    </div>
</div>

