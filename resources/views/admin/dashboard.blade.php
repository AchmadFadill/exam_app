@section('title', 'Dashboard Admin')

<div class="space-y-8">
    <!-- Hero & Quick Stats Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        <div class="lg:col-span-2 bg-bg-surface dark:bg-bg-surface rounded-[2rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main p-6 sm:p-10 flex flex-col md:flex-row items-center gap-6 sm:gap-10 relative overflow-hidden transition-all duration-300">
            <div class="relative z-10 flex-1">
                <x-page-header 
                    title="Halaman" 
                    highlight="Utama" 
                    subtitle="Ringkasan cepat aktivitas sistem hari ini"
                    :date="false"
                    :semester="false"
                />
            </div>
            
            <!-- Quick Stats Mini-Grid -->
            <div class="grid grid-cols-2 gap-3 sm:gap-5 w-full md:w-auto relative z-10">
                <div class="p-4 sm:p-6 bg-gray-50/50 dark:bg-slate-800/30 rounded-[1.2rem] sm:rounded-[1.5rem] border border-border-subtle dark:border-border-subtle flex flex-col items-center justify-center text-center shadow-inner group">
                    <div class="text-[8px] sm:text-[10px] text-text-muted font-black uppercase tracking-widest mb-1 sm:mb-2 opacity-60 group-hover:opacity-100 transition-opacity">UJIAN HARI INI</div>
                    <div class="text-2xl sm:text-3xl font-black text-text-main tracking-tighter">{{ $quick_stats['exams_today'] }}</div>
                </div>
                <div class="p-4 sm:p-6 bg-gray-50/50 dark:bg-slate-800/30 rounded-[1.2rem] sm:rounded-[1.5rem] border border-border-subtle dark:border-border-subtle flex flex-col items-center justify-center text-center shadow-inner group">
                    <div class="text-[8px] sm:text-[10px] text-text-muted font-black uppercase tracking-widest mb-1 sm:mb-2 opacity-60 group-hover:opacity-100 transition-opacity">SISWA AKTIF</div>
                    <div class="text-2xl sm:text-3xl font-black text-text-main tracking-tighter">{{ $quick_stats['active_students'] }}</div>
                </div>
                <div class="p-4 sm:p-6 bg-gray-50/50 dark:bg-slate-800/30 rounded-[1.2rem] sm:rounded-[1.5rem] border border-border-subtle dark:border-border-subtle flex flex-col items-center justify-center text-center shadow-inner group col-span-2">
                    <div class="text-[8px] sm:text-[10px] text-text-muted font-black uppercase tracking-widest mb-1 sm:mb-2 opacity-60 group-hover:opacity-100 transition-opacity">PERMINTAAN RESET PASSWORD</div>
                    <div class="text-2xl sm:text-3xl font-black text-text-main tracking-tighter">{{ $quick_stats['pending_password_requests'] }}</div>
                </div>
            </div>

            <!-- Background Decor -->
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-primary/5 rounded-full blur-3xl"></div>
        </div>

        <!-- School Info Card -->
        <div class="bg-gradient-to-br from-primary to-blue-700 rounded-[2rem] shadow-lg p-6 sm:p-8 text-white relative overflow-hidden flex flex-col justify-between">
             <div class="relative z-10">
                <h3 class="text-lg sm:text-xl font-bold">{{ $app_name ?? 'Sistem CBT' }}</h3>
                <p class="text-blue-100 text-sm mt-1 opacity-80">Portal Administrasi Utama</p>
                
                <div class="mt-8 space-y-3">
                    <div class="flex items-center justify-between text-sm py-2 border-b border-white/10">
                        <span class="opacity-70">Semester</span>
                         <span class="font-semibold">{{ $app_semester ?? 'Ganjil' }} {{ $app_academic_year ?? '' }}</span>
                    </div>
                     <div class="flex items-center justify-between text-sm py-2">
                        <span class="opacity-70">Tanggal</span>
                         <span class="font-semibold">{{ now()->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
            </div>

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
                    <span class="w-2 h-2 rounded-full {{ $stats['active_exams_count'] > 0 ? 'bg-red-500 animate-pulse' : 'bg-green-500' }}"></span>
                    Ujian Berjalan ({{ $stats['active_exams_count'] }})
                </h3>
                <x-button href="{{ route('admin.monitor') }}" variant="soft">
                    Selengkapnya
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </x-button>
            </div>

            <x-dashboard.active-exam-cards :exams="$active_exams" />
        </section>

        <!-- Security Activity -->
        <section>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Aktivitas Terbaru
                </h3>
                <x-button href="{{ route('admin.monitor') }}" variant="soft">
                        Selengkapnya
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </x-button>
            </div>
            
            <div class="bg-bg-surface dark:bg-bg-surface rounded-[2rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main overflow-hidden flex flex-col h-[500px]">
                <div class="p-6 border-b border-border-subtle dark:border-border-subtle bg-gray-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-text-main flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                        Umpan Langsung
                    </h3>
                    <span class="text-[10px] text-text-muted font-mono">Pembaruan Real-time</span>
                </div>

                <div class="p-6 overflow-y-auto space-y-4 flex-1">
                    @forelse($live_logs as $log)
                    <div wire:key="log-{{ $log['id'] ?? $log['timestamp'] }}" 
                         wire:transition.slide.down
                         class="flex gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                        <div class="mt-1 flex-shrink-0 w-2 h-2 rounded-full ring-2 ring-offset-2 ring-offset-white dark:ring-offset-slate-900 
                            {{ $log['type'] === 'warning' ? 'bg-amber-500 ring-amber-200' : '' }}
                            {{ $log['type'] === 'success' ? 'bg-green-500 ring-green-200' : '' }}
                            {{ $log['type'] === 'info' ? 'bg-blue-500 ring-blue-200' : '' }}
                            {{ $log['type'] === 'primary' ? 'bg-indigo-500 ring-indigo-200' : '' }}
                        "></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1">
                                <h5 class="text-sm font-black text-text-main truncate">{{ $log['student'] }}</h5>
                                <span class="text-[10px] font-mono text-text-muted flex-shrink-0 opacity-70">{{ $log['time'] }}</span>
                            </div>
                            <p class="text-xs text-text-muted line-clamp-2">{{ $log['activity'] }}</p>
                            <div class="mt-1.5 flex items-center gap-2">
                                <span class="text-[9px] uppercase font-black tracking-widest text-text-muted opacity-50">{{ $log['exam'] ?? 'Ujian Tidak Diketahui' }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="flex flex-col items-center justify-center h-full text-center opacity-50">
                        <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm font-bold text-gray-400">Tidak ada aktivitas terbaru.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <!-- Quick Management & System Summary -->
    <div class="bg-slate-900 rounded-[2.5rem] sm:rounded-[3rem] p-6 sm:p-12 text-white flex flex-col lg:flex-row items-center justify-between gap-8 sm:gap-12 relative overflow-hidden shadow-2xl shadow-slate-900/40 border border-white/5">
        <div class="relative z-10 lg:w-1/2 text-center lg:text-left w-full">
            <h2 class="text-2xl sm:text-4xl font-black mb-3 sm:mb-4 tracking-tighter italic uppercase">Panel <span class="text-primary not-italic">Kendali</span></h2>
            <p class="text-slate-400 text-sm sm:text-lg mb-6 sm:mb-10 leading-relaxed font-medium">Kelola sistem CBT mulai dari data pengguna hingga pengaturan server secara real-time.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-5">
                <a href="{{ route('admin.students') }}" class="px-8 py-5 bg-white/5 hover:bg-white/10 rounded-[1.5rem] text-center font-black border border-white/5 transition-all text-xs uppercase tracking-[0.2em] hover:scale-[1.02] active:scale-[1]">
                    Siswa & Kelas
                </a>
                <a href="{{ route('admin.teachers') }}" class="px-8 py-5 bg-white/5 hover:bg-white/10 rounded-[1.5rem] text-center font-black border border-white/5 transition-all text-xs uppercase tracking-[0.2em] hover:scale-[1.02] active:scale-[1]">
                    Data Guru
                </a>
                <a href="{{ route('admin.exams.create') }}" class="px-8 py-5 bg-primary hover:bg-blue-600 rounded-[1.5rem] text-center font-black transition-all shadow-2xl shadow-primary/40 text-xs uppercase tracking-[0.2em] hover:scale-[1.02] active:scale-[1]">
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

<script>
    document.addEventListener('livewire:initialized', () => {
        if (typeof Echo !== 'undefined') {
            Echo.channel('security-monitoring')
                .listen('.student-violation', (e) => {
                    console.log("🔥 [FIRE] Dashboard Received & Dispatching:", e);
                    Livewire.dispatch('manual-violation', { event: e });
                });
        }
    });
</script>
