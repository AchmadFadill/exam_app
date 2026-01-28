@section('title', 'Admin Dashboard')

<div class="space-y-8">
    <!-- Hero & System Health Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-bg-surface dark:bg-bg-surface rounded-[2rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main p-10 flex flex-col md:flex-row items-center gap-10 relative overflow-hidden transition-all duration-300">
            <div class="relative z-10 flex-1">
                <h1 class="text-4xl font-black text-text-main tracking-tight uppercase italic">Halaman <span class="text-primary not-italic">Utama</span></h1>
                <p class="text-text-muted mt-3 text-lg font-medium">Sistem berjalan dengan status <span class="text-green-600 font-black uppercase tracking-wider text-sm bg-green-50 dark:bg-green-500/10 px-3 py-1 rounded-full">{{ $system_health['status'] }}</span>.</p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <div class="flex items-center gap-3 px-5 py-2.5 bg-blue-50/50 dark:bg-primary/10 text-primary rounded-2xl border border-primary/10 font-bold text-sm">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Uptime: {{ $system_health['uptime'] }}
                    </div>
                </div>
            </div>
            
            <!-- Health Stats Mini-Grid -->
            <div class="grid grid-cols-2 gap-5 w-full md:w-auto relative z-10">
                <div class="p-6 bg-gray-50/50 dark:bg-slate-800/30 rounded-[1.5rem] border border-border-subtle dark:border-border-subtle flex flex-col items-center justify-center text-center shadow-inner group">
                    <div class="text-[10px] text-text-muted font-black uppercase tracking-widest mb-2 opacity-60 group-hover:opacity-100 transition-opacity">BEBAN CPU</div>
                    <div class="text-3xl font-black text-text-main tracking-tighter">{{ $system_health['cpu_load'] }}%</div>
                    <div class="w-16 bg-gray-200 dark:bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                        <div class="bg-primary h-full transition-all duration-700" style="width: {{ $system_health['cpu_load'] }}%"></div>
                    </div>
                </div>
                <div class="p-6 bg-gray-50/50 dark:bg-slate-800/30 rounded-[1.5rem] border border-border-subtle dark:border-border-subtle flex flex-col items-center justify-center text-center shadow-inner group">
                    <div class="text-[10px] text-text-muted font-black uppercase tracking-widest mb-2 opacity-60 group-hover:opacity-100 transition-opacity">PENGGUNAAN RAM</div>
                    <div class="text-3xl font-black text-text-main tracking-tighter">{{ $system_health['ram_usage'] }}%</div>
                    <div class="w-16 bg-gray-200 dark:bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                        <div class="bg-amber-500 h-full transition-all duration-700" style="width: {{ $system_health['ram_usage'] }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Background Decor -->
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-primary/5 rounded-full blur-3xl"></div>
        </div>

        <!-- School Info Card -->
        <div class="bg-gradient-to-br from-primary to-blue-700 rounded-2xl shadow-lg p-8 text-white relative overflow-hidden flex flex-col justify-between">
             <div class="relative z-10">
                <h3 class="text-xl font-bold">SMAIT Baitul Muslim</h3>
                <p class="text-blue-100 text-sm mt-1 opacity-80">Portal Administrasi Utama</p>
                
                <div class="mt-8 space-y-3">
                    <div class="flex items-center justify-between text-sm py-2 border-b border-white/10">
                        <span class="opacity-70">Semester</span>
                         <span class="font-semibold">Ganjil 24/25</span>
                    </div>
                     <div class="flex items-center justify-between text-sm py-2">
                        <span class="opacity-70">Tanggal</span>
                         <span class="font-semibold">{{ now()->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
            </div>
            <button class="mt-6 w-full py-2.5 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-bold transition-all border border-white/10">
                Kelola Institusi
            </button>
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        </div>
    </div>

    <!-- Main Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-card variant="stat" title="Total Siswa" :value="$stats['total_students']" subtitle="Semua Jenjang" color="primary">
             <x-slot name="icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
             </x-slot>
        </x-card>
        <x-card variant="stat" title="Guru Pengajar" :value="$stats['total_teachers']" subtitle="Pemberi Materi" color="secondary">
             <x-slot name="icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
             </x-slot>
        </x-card>
        <x-card variant="stat" title="Ujian Terjadwal" :value="$stats['total_exams']" subtitle="Selesai & Akan Datang" color="green">
             <x-slot name="icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
             </x-slot>
        </x-card>
        <x-card variant="stat" title="Total Soal" :value="$stats['total_questions']" subtitle="Dalam Bank Soal" color="amber">
             <x-slot name="icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
             </x-slot>
        </x-card>
    </div>

    <!-- Monitoring Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Global Live Activity -->
        <section>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 uppercase tracking-tight">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    Ujian Berjalan ({{ $stats['active_exams_count'] }})
                </h3>
                <a href="{{ route('admin.monitor') }}" class="text-sm font-semibold text-primary hover:text-blue-700">Selengkapnya &rarr;</a>
            </div>
            
            <div class="bg-bg-surface dark:bg-bg-surface rounded-[2rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main divide-y divide-border-subtle dark:divide-border-subtle overflow-hidden">
                @foreach($active_exams as $exam)
                <div class="p-8 hover:bg-gray-50/50 dark:hover:bg-slate-800/30 transition-colors">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <span class="px-3 py-1 bg-gray-100 dark:bg-slate-800 text-text-muted rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $exam['class'] }}</span>
                            <h4 class="text-xl font-black text-text-main mt-3 tracking-tight">{{ $exam['subject'] }}</h4>
                            <p class="text-sm text-text-muted mt-1 font-medium">Guru: {{ $exam['teacher'] }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-3xl font-black text-text-main tracking-tighter">{{ $exam['progress'] }}%</span>
                            <p class="text-[10px] text-text-muted font-black uppercase tracking-widest opacity-60">PROGRES</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-6">
                         <div class="flex-1 bg-gray-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden shadow-inner">
                            <div class="bg-primary h-full transition-all duration-1000 ease-out shadow-[0_0_12px_rgba(30,64,175,0.4)]" style="width: {{ $exam['progress'] }}%"></div>
                        </div>
                        <span class="text-xs font-black text-text-main whitespace-nowrap">{{ $exam['students_online'] }}/{{ $exam['total_students'] }} <span class="text-green-500">Live</span></span>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Security Activity -->
        <section>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Aktivitas Keamanan
                </h3>
            </div>
            
            <div class="bg-bg-surface dark:bg-bg-surface rounded-[2rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main overflow-hidden">
                <div class="p-8 space-y-4">
                    @foreach($alerts as $alert)
                    <div class="flex items-start gap-5 p-5 rounded-2xl border border-border-subtle dark:border-border-subtle hover:border-red-500/30 hover:bg-red-50/30 dark:hover:bg-red-500/5 transition-all group cursor-pointer">
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center 
                            @if($alert['severity'] == 'critical') bg-red-100 dark:bg-red-500/20 text-red-600 @else bg-amber-100 dark:bg-amber-500/20 text-amber-600 @endif font-black shadow-sm">
                            !
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start gap-4">
                                <h5 class="text-sm font-black text-text-main truncate uppercase tracking-tight">{{ $alert['user'] }}</h5>
                                <span class="text-[10px] text-text-muted font-black uppercase tracking-widest whitespace-nowrap opacity-60">{{ $alert['time'] }}</span>
                            </div>
                            <p class="text-[11px] text-text-muted mt-1 uppercase tracking-widest font-black opacity-70 group-hover:text-red-600 transition-colors">{{ $alert['event'] }} <span class="mx-1">•</span> {{ $alert['class'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                 <div class="p-6 bg-gray-50/50 dark:bg-slate-800/30 text-center border-t border-border-subtle dark:border-border-subtle">
                    <button class="text-xs font-black text-text-muted hover:text-primary uppercase tracking-[0.2em] transition-all">Selengkapnya &rarr;</button>
                </div>
            </div>
        </section>
    </div>

    <!-- Quick Management & System Summary -->
    <div class="bg-slate-900 rounded-[3rem] p-12 text-white flex flex-col lg:flex-row items-center justify-between gap-12 relative overflow-hidden shadow-2xl shadow-slate-900/40 border border-white/5">
        <div class="relative z-10 lg:w-1/2 text-center lg:text-left">
            <h2 class="text-4xl font-black mb-4 tracking-tighter italic uppercase">Panel <span class="text-primary not-italic">Kendali</span></h2>
            <p class="text-slate-400 text-lg mb-10 leading-relaxed font-medium">Kelola sistem CBT mulai dari data pengguna hingga pengaturan server secara real-time.</p>
            <div class="grid grid-cols-2 gap-5">
                <a href="{{ route('admin.students') }}" class="px-8 py-5 bg-white/5 hover:bg-white/10 rounded-[1.5rem] text-center font-black border border-white/5 transition-all text-xs uppercase tracking-[0.2em] hover:scale-[1.02] active:scale-[1]">
                    Siswa & Kelas
                </a>
                <a href="{{ route('admin.teachers') }}" class="px-8 py-5 bg-white/5 hover:bg-white/10 rounded-[1.5rem] text-center font-black border border-white/5 transition-all text-xs uppercase tracking-[0.2em] hover:scale-[1.02] active:scale-[1]">
                    Data Guru
                </a>
                <a href="{{ route('admin.exams') }}" class="px-8 py-5 bg-primary hover:bg-blue-600 rounded-[1.5rem] text-center font-black transition-all shadow-2xl shadow-primary/40 text-xs uppercase tracking-[0.2em] hover:scale-[1.02] active:scale-[1]">
                    Buat Ujian Baru
                </a>
                <a href="{{ route('admin.settings') }}" class="px-8 py-5 bg-white/5 hover:bg-white/10 rounded-[1.5rem] text-center font-black border border-white/5 transition-all text-xs uppercase tracking-[0.2em] hover:scale-[1.02] active:scale-[1]">
                    Pengaturan Sistem
                </a>
            </div>
        </div>
        
        <div class="relative z-10 lg:w-1/3 flex flex-col items-center">
             <div class="w-56 h-56 bg-gradient-to-tr from-primary via-blue-600 to-indigo-700 rounded-full flex items-center justify-center p-1.5 shadow-2xl relative group">
                <div class="w-full h-full bg-slate-900 rounded-full flex flex-col items-center justify-center text-center transition-transform duration-700 group-hover:rotate-12">
                    <span class="text-5xl font-black tracking-tighter">{{ $stats['active_exams_count'] }}</span>
                    <span class="text-[10px] uppercase font-black text-slate-500 mt-2 tracking-[0.3em]">Ujian Aktif</span>
                </div>
                <!-- Moving particle mask or simple ping -->
                <div class="absolute inset-0 border-4 border-primary/20 rounded-full animate-ping pointer-events-none"></div>
                <div class="absolute -inset-4 bg-primary/10 rounded-full blur-[40px] opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
             </div>
             <p class="mt-10 text-center text-sm text-slate-400 italic font-bold opacity-60">"Eksperimen dengan keunggulan, eksekusi dengan presisi."</p>
        </div>

        <!-- Decorative elements -->
        <div class="absolute -right-20 -top-20 w-96 h-96 bg-primary opacity-20 blur-[120px] rounded-full"></div>
        <div class="absolute -left-20 -bottom-20 w-96 h-96 bg-blue-600 opacity-10 blur-[120px] rounded-full"></div>
    </div>
</div>
