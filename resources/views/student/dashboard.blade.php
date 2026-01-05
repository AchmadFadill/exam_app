@section('title', 'Dashboard Siswa')


<div class="space-y-8">
    <!-- Hero Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $greeting }}, {{ auth()->user()->name ?? 'Siswa' }}! 👋</h1>
            <p class="text-gray-500 mt-1">Siap untuk mengerjakan ujian hari ini? Semangat!</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-white border border-gray-200 text-gray-600 rounded-full text-sm font-medium shadow-sm">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>

    <!-- Active Exams (Ujian Tersedia) - HERO FEATURE -->
    <section>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
                Ujian Tersedia ({{ count($active_exams) }})
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($active_exams as $exam)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow relative group">
                 @if($exam['is_urgent'])
                <div class="absolute top-0 right-0 mt-4 mr-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 animate-pulse">
                        Segera Berakhir
                    </span>
                </div>
                @endif
                
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 tracking-wide uppercase">
                                {{ $exam['subject'] }}
                            </span>
                            <h4 class="mt-3 text-xl font-bold text-gray-900 group-hover:text-primary transition-colors">
                                {{ $exam['title'] }}
                            </h4>
                            <p class="text-gray-500 text-sm mt-1">{{ $exam['teacher'] }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-6 text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ $exam['duration'] }} Menit</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <span>{{ $exam['questions_count'] }} Soal</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-100 flex items-center justify-between">
                        <div class="text-sm">
                            <p class="text-gray-500">Batas Waktu</p>
                            <p class="font-semibold text-gray-900">{{ $exam['end_time'] }} WIB</p>
                        </div>
                        <a href="{{ route('student.exam.start', $exam['id']) }}" class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-primary hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform group-hover:translate-x-1">
                            Kerjakan Sekarang
                            <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </a>
                    </div>
                </div>
                <div class="h-1.5 w-full bg-gray-100">
                    <div class="h-1.5 bg-primary rounded-r-full" style="width: 45%"></div> <!-- Dummy progress indicator -->
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white p-8 rounded-2xl shadow-sm border border-gray-200 text-center">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Tidak ada ujian aktif</h3>
                <p class="text-gray-500 mt-1">Anda bisa bersantai sejenak! 🎉</p>
            </div>
            @endforelse
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Upcoming Exams -->
        <div class="lg:col-span-2 space-y-6">
            <h3 class="text-lg font-bold text-gray-900">Jadwal Mendatang</h3>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 divide-y divide-gray-100">
                @forelse($upcoming_exams as $exam)
                <div class="p-5 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                    <div class="flex-shrink-0 w-16 h-16 bg-gray-50 rounded-lg flex flex-col items-center justify-center border border-gray-100">
                         <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-semibold text-primary bg-blue-50 px-2 py-0.5 rounded">{{ $exam['subject'] }}</span>
                        </div>
                        <h4 class="text-base font-bold text-gray-900 truncate">{{ $exam['title'] }}</h4>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $exam['date'] }}</p>
                    </div>
                     <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            Akan Datang
                        </span>
                    </div>
                </div>
                @empty
                 <div class="p-6 text-center text-gray-500 text-sm">
                    Belum ada jadwal ujian mendatang.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Student Stats -->
        <div class="space-y-6">
            <h3 class="text-lg font-bold text-gray-900">Statistik Belajar</h3>
            
            <div class="bg-gradient-to-br from-primary to-blue-600 rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-blue-100 text-sm font-medium">Rata-rata Nilai</p>
                    <h2 class="text-4xl font-bold mt-1">{{ $stats['avg_score'] }}</h2>
                    <div class="mt-4 flex items-center gap-2 text-sm text-blue-100 bg-white/10 px-3 py-1.5 rounded-lg w-fit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        <span>Top 10% di kelas</span>
                    </div>
                </div>
                <div class="absolute right-0 top-0 w-32 h-32 bg-white rounded-full opacity-10 blur-3xl -mr-10 -mt-10"></div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h4 class="font-bold text-gray-900 mb-4">Ringkasan Aktivitas</h4>
                <div class="space-y-4">
                     <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Ujian Selesai</span>
                        <span class="font-bold text-gray-900">{{ $stats['completed_exams'] }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 80%"></div>
                    </div>
                    
                    <div class="flex items-center justify-between text-sm pt-2">
                        <span class="text-gray-500">Kehadiran Ujian</span>
                        <span class="font-bold text-gray-900">{{ $stats['attendance'] }}%</span>
                    </div>
                     <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $stats['attendance'] }}%"></div>
                    </div>
                </div>
                 <button class="w-full mt-6 py-2 px-4 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                    Lihat Riwayat Lengkap
                </button>
            </div>
        </div>
    </div>
</div>

