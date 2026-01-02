@section('title', 'Dashboard')

<div class="space-y-8">
    <!-- Hero Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $greeting }}, {{ auth()->user()->name ?? 'Guru' }}! 👋</h1>
            <p class="text-gray-500 mt-1">Ini ringkasan aktivitas ujian hari ini, {{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 bg-blue-50 text-primary text-sm font-medium rounded-full border border-blue-100">
                Semester Ganjil 2025/2026
            </span>
        </div>
    </div>

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
                    <a href="#" class="text-sm font-medium text-primary hover:text-blue-700">Lihat Semua Monitor &rarr;</a>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 divide-y divide-gray-100">
                    @forelse($ongoing_exams as $exam)
                    <div class="p-5 hover:bg-gray-50 transition-colors group">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <!-- Exam Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-600">{{ $exam['class'] }}</span>
                                    <span class="text-xs text-gray-400">•</span>
                                    <span class="text-xs font-medium text-primary">{{ $exam['subject'] }}</span>
                                </div>
                                <h4 class="font-bold text-gray-900 group-hover:text-primary transition-colors text-lg">{{ $exam['name'] }}</h4>
                                <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ $exam['start_time'] }} - {{ $exam['end_time'] }}
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        {{ $exam['finished_students'] }}/{{ $exam['total_students'] }} Selesai
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Progress Circle / Action -->
                            <div class="flex items-center gap-4">
                                <div class="text-right hidden sm:block">
                                    <div class="text-2xl font-bold text-gray-900">{{ $exam['percentage'] }}%</div>
                                    <div class="text-xs text-gray-500">Progress</div>
                                </div>
                                <div class="w-px h-10 bg-gray-200 hidden sm:block"></div>
                                <a href="#" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all text-sm shadow-sm">
                                    Detail
                                </a>
                                <a href="#" class="px-4 py-2 bg-primary text-white font-medium rounded-lg hover:bg-blue-700 transition-colors text-sm shadow-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Pantau
                                </a>
                            </div>
                        </div>
                        <!-- Progress Bar -->
                        <div class="mt-4 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-primary h-1.5 rounded-full" style="width: {{ $exam['percentage'] }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-gray-500">
                        <p>Tidak ada ujian yang sedang berlangsung saat ini.</p>
                    </div>
                    @endforelse
                </div>
            </section>

            <!-- Quick Actions Grid -->
             <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('teacher.question-bank.index') }}" class="group p-6 bg-gradient-to-br from-indigo-500 to-primary rounded-xl shadow-md text-white relative overflow-hidden transition-all hover:shadow-lg hover:-translate-y-1">
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4 backdrop-blur-sm group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <h4 class="font-bold text-xl">Buat Soal Baru</h4>
                        <p class="text-indigo-100 text-sm mt-1 opacity-90">Tambahkan koleksi soal ke bank soal</p>
                    </div>
                    <div class="absolute right-0 bottom-0 opacity-10 group-hover:opacity-20 transition-opacity transform translate-x-1/4 translate-y-1/4 rotate-12">
                        <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                </a>

                <a href="{{ route('teacher.exams.index') }}" class="group p-6 bg-white border border-gray-200 rounded-xl shadow-sm hover:border-primary/30 hover:shadow-md transition-all hover:-translate-y-1 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h4 class="font-bold text-xl text-gray-900">Jadwalkan Ujian</h4>
                        <p class="text-gray-500 text-sm mt-1">Buat jadwal ujian baru untuk kelas</p>
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
                     <a href="#" class="text-xs font-medium text-gray-500 hover:text-primary">Lihat Kalender</a>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-2">
                    @foreach($upcoming_exams as $exam)
                    <div class="flex gap-4 p-3 hover:bg-gray-50 rounded-lg transition-colors group cursor-pointer">
                        <div class="flex-shrink-0 w-14 flex flex-col items-center justify-center bg-blue-50 text-primary rounded-lg">
                            <span class="text-xs font-bold uppercase">{{ Str::substr($exam['date'], 0, 3) }}</span>
                            <span class="text-lg font-bold">{{ filter_var($exam['date'], FILTER_SANITIZE_NUMBER_INT) ?: '03' }}</span>
                        </div>
                        <div class="flex-1 min-w-0 py-0.5">
                            <h5 class="text-sm font-bold text-gray-900 truncate group-hover:text-primary transition-colors">{{ $exam['name'] }}</h5>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $exam['class'] }} • {{ $exam['time'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            <!-- Recent Activity -->
            <section>
                <h3 class="text-lg font-bold text-gray-900 mb-4">Aktivitas Terkini</h3>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($recent_activities as $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-100" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-4 ring-white
                                                @if($activity['type'] == 'success') bg-green-100 text-green-600
                                                @elseif($activity['type'] == 'warning') bg-amber-100 text-amber-600
                                                @elseif($activity['type'] == 'info') bg-blue-100 text-blue-600
                                                @else bg-gray-100 text-gray-500 @endif">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex flex-col">
                                            <p class="text-sm text-gray-900 font-medium">{{ $activity['action'] }}</p>
                                            <span class="text-xs text-gray-400 mt-0.5">{{ $activity['time'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

