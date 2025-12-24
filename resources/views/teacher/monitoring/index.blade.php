@section('title', 'Dashboard Monitoring')

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-bold text-2xl text-text-main">Dashboard Monitoring</h2>
            <p class="text-text-muted text-sm">Pantau ujian siswa Anda yang sedang berlangsung</p>
        </div>
    </div>

    <!-- Active Exams Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($activeExams as $exam)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-300 relative group">
            <!-- Header -->
            <div class="p-5 border-b border-gray-50 bg-gradient-to-r from-white to-blue-50/30">
                <div class="flex justify-between items-start mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 animate-pulse">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                        Live
                    </span>
                    <span class="text-xs font-semibold text-gray-400 font-mono">{{ $exam['start_time'] }} - {{ $exam['end_time'] }}</span>
                </div>
                <h3 class="font-bold text-lg text-text-main leading-tight mb-1 group-hover:text-primary transition-colors">{{ $exam['name'] }}</h3>
                <p class="text-sm text-text-muted">{{ $exam['subject'] }} • {{ $exam['class'] }}</p>
            </div>

            <!-- Stats -->
            <div class="p-5 grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-text-main">{{ $exam['total_students'] }}</div>
                    <div class="text-[10px] uppercase tracking-wider text-text-muted font-semibold mt-1">Total</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-primary">{{ $exam['working'] }}</div>
                    <div class="text-[10px] uppercase tracking-wider text-text-muted font-semibold mt-1">Mengerjakan</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600">{{ $exam['finished'] }}</div>
                    <div class="text-[10px] uppercase tracking-wider text-text-muted font-semibold mt-1">Selesai</div>
                </div>
            </div>

            <!-- Action -->
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100">
                <a href="{{ route('teacher.monitoring.detail', $exam['id']) }}" class="flex justify-center items-center gap-2 w-full bg-primary hover:bg-blue-700 text-white text-sm font-medium py-2.5 rounded-lg transition-all shadow-sm hover:shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    Pantau Sekarang
                </a>
            </div>
        </div>
        @endforeach
        
        <!-- Empty State Placeholder (Optional) -->
        @if(count($activeExams) === 0)
        <div class="col-span-full py-12 text-center text-text-muted bg-gray-50 rounded-xl border border-dashed border-gray-200">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p>Tidak ada ujian yang sedang berlangsung saat ini.</p>
        </div>
        @endif
    </div>
</div>
