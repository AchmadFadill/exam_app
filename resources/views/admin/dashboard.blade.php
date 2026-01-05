@section('title', 'Admin Dashboard')

<div class="space-y-8">
    <!-- Hero & System Health Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 p-8 flex flex-col md:flex-row items-center gap-8 relative overflow-hidden">
            <div class="relative z-10 flex-1">
                <h1 class="text-3xl font-bold text-gray-900">{{ $greeting }}, Admin! 👋</h1>
                <p class="text-gray-500 mt-2 text-lg">Sistem CBT berjalan dengan status <span class="text-green-600 font-bold uppercase tracking-wider">{{ $system_health['status'] }}</span>.</p>
                <div class="mt-6 flex flex-wrap gap-4">
                    <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-xl border border-blue-100 font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Uptime: {{ $system_health['uptime'] }}
                    </div>
                </div>
            </div>
            
            <!-- Health Stats Mini-Grid -->
            <div class="grid grid-cols-2 gap-4 w-full md:w-auto relative z-10">
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex flex-col items-center justify-center text-center">
                    <div class="text-xs text-gray-500 font-semibold uppercase tracking-widest mb-1">CPU LOAD</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $system_health['cpu_load'] }}%</div>
                    <div class="w-16 bg-gray-200 h-1.5 rounded-full mt-2 overflow-hidden">
                        <div class="bg-blue-500 h-full" style="width: {{ $system_health['cpu_load'] }}%"></div>
                    </div>
                </div>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex flex-col items-center justify-center text-center">
                    <div class="text-xs text-gray-500 font-semibold uppercase tracking-widest mb-1">RAM USAGE</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $system_health['ram_usage'] }}%</div>
                    <div class="w-16 bg-gray-200 h-1.5 rounded-full mt-2 overflow-hidden">
                        <div class="bg-purple-500 h-full" style="width: {{ $system_health['ram_usage'] }}%"></div>
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
                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    Live Monitoring ({{ $stats['active_exams_count'] }})
                </h3>
                <a href="{{ route('admin.monitor') }}" class="text-sm font-semibold text-primary hover:text-blue-700">Manajemen Full &rarr;</a>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 divide-y divide-gray-100 overflow-hidden">
                @foreach($active_exams as $exam)
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-bold">{{ $exam['class'] }}</span>
                            <h4 class="text-lg font-bold text-gray-900 mt-1">{{ $exam['subject'] }}</h4>
                            <p class="text-sm text-gray-500 mt-0.5">Guru: {{ $exam['teacher'] }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black text-gray-900">{{ $exam['progress'] }}%</span>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Selesai</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                         <div class="flex-1 bg-gray-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-primary h-full transition-all duration-500" style="width: {{ $exam['progress'] }}%"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-600 whitespace-nowrap">{{ $exam['students_online'] }}/{{ $exam['total_students'] }} Online</span>
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
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 space-y-4">
                    @foreach($alerts as $alert)
                    <div class="flex items-start gap-4 p-4 rounded-xl border border-gray-100 hover:border-red-100 hover:bg-red-50 transition-all group cursor-pointer">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center 
                            @if($alert['severity'] == 'critical') bg-red-100 text-red-600 @else bg-amber-100 text-amber-600 @endif font-bold">
                            !
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <h5 class="text-sm font-bold text-gray-900">{{ $alert['user'] }}</h5>
                                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $alert['time'] }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold group-hover:text-red-700">{{ $alert['event'] }} - {{ $alert['class'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                 <div class="p-4 bg-gray-50 text-center border-t border-gray-100">
                    <button class="text-sm font-bold text-gray-600 hover:text-primary transition-colors">Lihat Semua Laporan Keamanan &rarr;</button>
                </div>
            </div>
        </section>
    </div>

    <!-- Quick Management & System Summary -->
    <div class="bg-gray-900 rounded-3xl p-10 text-white flex flex-col lg:flex-row items-center justify-between gap-10 relative overflow-hidden">
        <div class="relative z-10 lg:w-1/2">
            <h2 class="text-3xl font-black mb-4">Siap Mengelola Hari Ini?</h2>
            <p class="text-gray-400 text-lg mb-8 leading-relaxed">Kelola seluruh aspek sistem mulai dari akun siswa, database soal, hingga monitoring ujian secara terpusat dan aman.</p>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('admin.students') }}" class="px-6 py-4 bg-white/10 hover:bg-white/20 rounded-2xl text-center font-bold border border-white/5 transition-all">
                    Siswa & Kelas
                </a>
                <a href="{{ route('admin.teachers') }}" class="px-6 py-4 bg-white/10 hover:bg-white/20 rounded-2xl text-center font-bold border border-white/5 transition-all">
                    Data Pengajar
                </a>
                <a href="{{ route('admin.exams') }}" class="px-6 py-4 bg-primary hover:bg-blue-600 rounded-2xl text-center font-bold transition-all shadow-xl shadow-primary/20">
                    Buka Ujian New
                </a>
                <a href="{{ route('admin.settings') }}" class="px-6 py-4 bg-white/10 hover:bg-white/20 rounded-2xl text-center font-bold border border-white/5 transition-all">
                    Config Sistem
                </a>
            </div>
        </div>
        
        <div class="relative z-10 lg:w-1/3 flex flex-col items-center">
             <div class="w-48 h-48 bg-gradient-to-tr from-blue-500 to-purple-600 rounded-full flex items-center justify-center p-1 shadow-2xl relative">
                <div class="w-full h-full bg-gray-900 rounded-full flex flex-col items-center justify-center text-center">
                    <span class="text-4xl font-black">{{ $stats['active_exams_count'] }}</span>
                    <span class="text-[10px] uppercase font-bold text-gray-400 mt-1">Ujian Aktif</span>
                </div>
                <!-- Animated ring -->
                <div class="absolute inset-0 border-4 border-blue-400/20 rounded-full animate-ping"></div>
             </div>
             <p class="mt-8 text-center text-sm text-gray-500 italic font-medium line-clamp-2">"Lakukan pengecekan berkala pada server saat ujian besar berlangsung."</p>
        </div>

        <!-- Decorative elements -->
        <div class="absolute right-0 top-0 h-full w-1/4 bg-primary opacity-5 blur-3xl rounded-full"></div>
    </div>
</div>
