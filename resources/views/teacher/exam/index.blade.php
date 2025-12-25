@section('title', 'Kelola Ujian')

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-bold text-2xl text-text-main">Kelola Ujian</h2>
            <p class="text-text-muted text-sm">Buat dan atur jadwal ujian untuk siswa</p>
        </div>
        <div>
            <a href="{{ route('teacher.exams.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat Ujian Baru
            </a>
        </div>
    </div>

    <!-- Exam List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($exams as $exam)
        <div class="bg-bg-surface rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
            <div class="p-5 flex-1">
                <div class="flex justify-between items-start mb-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        @if($exam['status'] == 'completed') bg-gray-100 text-gray-800
                        @elseif($exam['status'] == 'ongoing') bg-green-100 text-green-800 animate-pulse
                        @else bg-blue-100 text-blue-800 @endif">
                        @if($exam['status'] == 'completed') Selesai
                        @elseif($exam['status'] == 'ongoing') Sedang Berlangsung
                        @else Terjadwal @endif
                    </span>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                        </button>
                        <div x-show="open" class="absolute right-0 mt-2 w-48 bg-bg-surface rounded-md shadow-lg py-1 z-10 border border-gray-100">
                            <a href="{{ route('teacher.exams.edit', $exam['id']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Duplikasi</a>
                            <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Hapus</a>
                        </div>
                    </div>
                </div>

                <h3 class="font-bold text-lg text-text-main mb-1">{{ $exam['name'] }}</h3>
                <p class="text-sm text-text-muted mb-4">{{ $exam['subject'] }} - {{ $exam['class'] }}</p>

                <div class="space-y-3">
                    <div class="flex items-center text-sm text-text-muted">
                        <svg class="w-4 h-4 mr-2 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ date('d M Y', strtotime($exam['date'])) }}
                    </div>
                    <div class="flex items-center text-sm text-text-muted">
                        <svg class="w-4 h-4 mr-2 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $exam['duration'] }} Menit
                    </div>
                    <div class="flex items-center text-sm text-text-muted">
                        <svg class="w-4 h-4 mr-2 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        {{ $exam['questions_count'] }} Soal
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 grid grid-cols-2 gap-3 mt-auto">
                @if($exam['status'] == 'ongoing')
                    <a href="{{ route('teacher.monitoring.detail', $exam['id']) }}" class="col-span-2 flex justify-center items-center gap-2 bg-primary hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        Monitoring Live
                    </a>
                @elseif($exam['status'] == 'completed')
                    <a href="{{ route('teacher.grading.index') }}" class="flex justify-center items-center gap-2 bg-bg-surface border border-gray-200 hover:bg-gray-50 text-text-main text-sm font-medium py-2 rounded-lg transition-colors">
                        Lihat Nilai
                    </a>
                    <a href="{{ route('teacher.reports.index') }}" class="flex justify-center items-center gap-2 bg-bg-surface border border-gray-200 hover:bg-gray-50 text-text-main text-sm font-medium py-2 rounded-lg transition-colors">
                        Laporan
                    </a>
                @else
                    <a href="{{ route('teacher.exams.edit', $exam['id']) }}" class="col-span-2 flex justify-center items-center gap-2 bg-bg-surface border border-gray-200 hover:bg-gray-50 text-text-main text-sm font-medium py-2 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit Ujian
                    </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

