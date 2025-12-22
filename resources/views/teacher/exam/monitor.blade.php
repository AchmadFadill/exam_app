@section('title', 'Monitoring Ujian Live')

<div wire:poll.10s class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('teacher.exams.index') }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-text-main">Monitoring Ujian</h2>
                <p class="text-text-muted text-sm">Ujian Harian Matematika - XI IPA 1</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
             <div class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium flex items-center gap-2 animate-pulse">
                <span class="w-2 h-2 bg-green-600 rounded-full"></span>
                Live Update
             </div>
             <div class="text-text-muted font-mono text-xl">
                 00:45:12
             </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-bg-surface p-4 rounded-xl border border-gray-100 shadow-sm">
            <p class="text-xs text-text-muted uppercase font-semibold">Total Siswa</p>
            <p class="text-2xl font-bold text-text-main">32</p>
        </div>
        <div class="bg-bg-surface p-4 rounded-xl border border-gray-100 shadow-sm">
             <p class="text-xs text-text-muted uppercase font-semibold">Sedang Mengerjakan</p>
             <p class="text-2xl font-bold text-primary">25</p>
        </div>
         <div class="bg-bg-surface p-4 rounded-xl border border-gray-100 shadow-sm">
             <p class="text-xs text-text-muted uppercase font-semibold">Selesai</p>
             <p class="text-2xl font-bold text-green-600">6</p>
        </div>
         <div class="bg-bg-surface p-4 rounded-xl border border-gray-100 shadow-sm">
             <p class="text-xs text-text-muted uppercase font-semibold">Belum Mulai</p>
             <p class="text-2xl font-bold text-gray-400">1</p>
        </div>
    </div>

    <!-- Student Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($students as $student)
        <div class="bg-bg-surface rounded-lg border {{ $student['tab_alert'] >= 3 ? 'border-red-300 ring-2 ring-red-100' : 'border-gray-200' }} p-4 shadow-sm relative overflow-hidden">
            @if($student['status'] == 'completed')
                <div class="absolute top-0 right-0 p-1.5 bg-green-500 rounded-bl-lg">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
            @endif

            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-text-muted font-bold text-sm">
                    {{ substr($student['name'], 0, 2) }}
                </div>
                <div>
                     <h4 class="font-semibold text-text-main text-sm truncate w-32 md:w-40">{{ $student['name'] }}</h4>
                     <p class="text-xs {{ $student['status'] == 'working' ? 'text-primary' : 'text-text-muted' }}">
                         @if($student['status'] == 'working') Sedang Mengerjakan
                         @elseif($student['status'] == 'completed') Selesai
                         @else Belum Mulai @endif
                     </p>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-gray-100 rounded-full h-2.5 mb-2">
                <div class="bg-primary h-2.5 rounded-full transition-all duration-500" style="width: {{ $student['w'] }}"></div>
            </div>
            <div class="flex justify-between text-xs text-text-muted mb-3">
                <span>Progress: {{ $student['progress'] }}</span>
                <span>{{ $student['w'] }}</span>
            </div>

            <!-- Alerts -->
            @if($student['tab_alert'] > 0)
            <div class="bg-amber-50 rounded px-2 py-1 flex items-center gap-2 text-xs text-amber-700 mb-3">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Pindah Tab: <strong>{{ $student['tab_alert'] }}x</strong>
            </div>
            @endif

            <!-- Actions -->
            <div class="grid grid-cols-2 gap-2 mt-2">
                <button class="px-2 py-1 text-xs border border-gray-200 rounded text-text-main hover:bg-gray-50">Detail</button>
                @if($student['status'] == 'working')
                <button class="px-2 py-1 text-xs bg-red-50 text-red-600 rounded hover:bg-red-100">Stop</button>
                @else
                <button class="px-2 py-1 text-xs border border-gray-200 rounded text-gray-300 cursor-not-allowed">Stop</button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

