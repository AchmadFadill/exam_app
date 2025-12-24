@section('title', 'Dashboard')

<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Stat Card 1 -->
        <!-- Stat Card 1: Active Exams (Red) -->
        <x-card variant="stat" title="Ujian (Sedang Aktif)" :value="$stats['active_exams']" :subtitle="($stats['ongoing_exams_count'] ?? 2) . ' Kelas'" color="red">
             <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
             </x-slot>
        </x-card>

        <x-card variant="stat" title="Ujian Selesai" :value="$stats['completed_exams']" subtitle="Minggu ini" color="green">
             <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
             </x-slot>
        </x-card>

        <x-card variant="stat" title="Total Siswa" :value="$stats['total_students']" subtitle="+12 Siswa baru" color="primary">
             <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
             </x-slot>
        </x-card>

        <x-card variant="stat" title="Total Soal" :value="$stats['questions_count']" subtitle="Semua soal" color="amber">
             <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
             </x-slot>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content Column (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ongoing Exams -->
            <!-- Ongoing Exams -->
            <x-card title="Ujian Berlangsung">
                <x-slot name="header_actions">
                    <a href="#" class="text-sm text-primary hover:text-blue-700 font-medium">Lihat Semua</a>
                </x-slot>
                
                <div class="divide-y divide-gray-50 -mx-6 -my-6">
                    @foreach($ongoing_exams as $exam)
                    <div class="p-5 hover:bg-gray-50 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h4 class="font-semibold text-text-main">{{ $exam['name'] }}</h4>
                                <div class="flex items-center gap-3 mt-1 text-sm text-text-muted">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        {{ $exam['class'] }}
                                    </span>
                                    <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Berakhir: {{ $exam['end_time'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 animate-pulse">
                                    Berlangsung
                                </span>
                                <p class="text-sm font-medium text-slate-600 mt-1">{{ $exam['progress'] }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-3">
                            <a href="#" class="flex-1 bg-primary hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-lg text-center transition-colors">
                                Monitor
                            </a>
                            <a href="#" class="flex-1 bg-white border border-gray-200 hover:bg-gray-50 text-text-main text-sm font-medium py-2 px-3 rounded-lg text-center transition-colors">
                                Detail
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-card>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('teacher.question-bank.index') }}" class="group p-5 bg-gradient-to-br from-primary to-blue-600 rounded-xl shadow-sm text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mb-3 backdrop-blur-sm group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <h4 class="font-bold text-lg">Buat Soal Baru</h4>
                        <p class="text-blue-100 text-sm mt-1">Tambahkan soal ke bank soal</p>
                    </div>
                    <div class="absolute right-0 bottom-0 oMoitoracity-10 group-hover:opacity-20 transition-opacity transform translate-x-1/4 translate-y-1/4">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
                    </div>
                </a>

                <a href="{{ route('teacher.exams.index') }}" class="group p-5 bg-bg-surface border border-gray-200 rounded-xl shadow-sm hover:border-blue-200 hover:shadow-md transition-all">
                    <div class="w-10 h-10 bg-amber-50 text-secondary rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h4 class="font-bold text-lg text-text-main">Jadwalkan Ujian</h4>
                    <p class="text-text-muted text-sm mt-1">Buat jadwal ujian baru untuk kelas</p>
                </a>
            </div>
        </div>

        <!-- Sidebar Column (1/3) -->
        <div class="space-y-6">
            <!-- Recent Activity -->
            <!-- Recent Activity -->
            <x-card title="Aktivitas Terkini">
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach($recent_activities as $activity)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                            @if($activity['type'] == 'success') bg-green-100 text-green-600
                                            @elseif($activity['type'] == 'warning') bg-amber-100 text-amber-600
                                            @elseif($activity['type'] == 'info') bg-blue-100 text-blue-600
                                            @else bg-gray-100 text-gray-500 @endif">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-text-main">{{ $activity['action'] }}</p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-text-muted">
                                            <time>{{ $activity['time'] }}</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </x-card>
            
            <!-- Calendar Widget (Visual Only) -->
            <div class="bg-primary rounded-xl shadow-lg p-6 text-white overflow-hidden relative">
                 <div class="relative z-10">
                    <h4 class="font-bold text-lg mb-2">Desember 2025</h4>
                    <div class="grid grid-cols-7 gap-2 text-center text-sm mb-2 opacity-70">
                        <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                    </div>
                    <div class="grid grid-cols-7 gap-2 text-center text-sm font-medium">
                        <span class="opacity-30">30</span>
                        <span>1</span>
                        <span>2</span>
                        <span class="bg-blue-600 rounded-full w-7 h-7 flex items-center justify-center mx-auto">3</span>
                        <span>4</span>
                        <span>5</span>
                        <span>6</span>
                    </div>
                    <div class="mt-4 pt-4 border-t border-blue-500/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center font-bold">
                                22
                            </div>
                            <div>
                                <p class="text-sm font-semibold">Ujian Akhir Semester</p>
                                <p class="text-xs text-indigo-200">Dimulai 08:00 WIB</p>
                            </div>
                        </div>
                    </div>
                 </div>
                 
                 <!-- Decor -->
                 <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-blue-600 rounded-full opacity-30 blur-2xl"></div>
                 <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-blue-400 rounded-full opacity-30 blur-2xl"></div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
