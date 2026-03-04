@section('title', 'Monitoring Ujian')

<div class="space-y-6" wire:poll.5s x-on:confirmed-force-submit.window="$wire.forceSubmit($event.detail)">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6 sm:gap-4">
        <div class="flex items-center gap-3 sm:gap-4">
            <x-button href="{{ route($backRoute) }}" variant="secondary" size="sm" square="true" class="!rounded-xl group">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </x-button>
            <div class="min-w-0">
                <h2 class="font-black text-xl sm:text-2xl text-text-main leading-tight truncate uppercase tracking-tight italic">{{ $exam->name }}</h2>
                <div class="flex items-center gap-2 mt-0.5">
                    <p class="text-[10px] sm:text-sm font-bold text-primary truncate max-w-[150px] sm:max-w-none">{{ $exam->subject->name }}</p>
                    <span class="text-gray-300">•</span>
                    <p class="text-[10px] sm:text-sm font-black text-text-muted truncate max-w-[150px] sm:max-w-none uppercase tracking-widest italic">{{ $exam->classrooms->pluck('name')->join(', ') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 mb-8">
        <!-- Token Card -->
        @if($exam->token)
        <div x-data="{ copied: false }" 
             @click="navigator.clipboard.writeText('{{ $exam->token }}'); copied = true; setTimeout(() => copied = false, 2000)" 
             class="bg-gradient-to-br from-indigo-700 via-primary to-blue-600 rounded-2xl p-4 sm:p-5 text-white shadow-xl shadow-primary/20 flex flex-col justify-center items-center relative overflow-hidden group cursor-pointer hover:scale-[1.02] transition-all active:scale-95 border border-white/10">
            <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            
            <!-- Default State -->
            <div x-show="!copied" class="flex flex-col items-center">
                <div class="text-[8px] sm:text-[10px] uppercase tracking-[0.2em] font-black opacity-80 mb-1 flex items-center gap-1">
                    Token Ujian
                    <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                </div>
                <div class="font-mono text-xl sm:text-3xl font-black tracking-widest group-hover:scale-110 transition-transform italic">{{ $exam->token }}</div>
            </div>

            <!-- Copied State -->
            <div x-show="copied" x-cloak class="flex flex-col items-center animate-pulse">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                <div class="text-[8px] sm:text-xs font-black uppercase tracking-widest">Tersalin!</div>
            </div>
        </div>
        @else
        <x-monitor.summary-card label="Token" value="-" variant="gray" />
        @endif

        <x-monitor.summary-card label="Total Siswa" :value="$exam->classrooms->sum('students_count')" />
        <x-monitor.summary-card label="Mengerjakan" :value="$students->where('status', 'in_progress')->count()" variant="primary" />
        <x-monitor.summary-card label="Diblokir" :value="$students->where('status', 'blocked')->count()" variant="danger" />
        <x-monitor.summary-card label="Selesai" :value="$students->whereIn('status', ['completed', 'graded', 'submitted'])->count()" variant="success" />
        <x-monitor.summary-card label="Belum Mulai" :value="$students->where('status', 'not_started')->count()" variant="gray" />
    </div>

    <!-- Filter Bar -->
    <x-monitor.filter-bar :classes="$classes" />

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Student Grid -->
        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($students as $student)
                <x-monitor.student-card :student="$student">
                    <x-button href="{{ route($student['detail_route'], ['examId' => $exam['id'], 'studentId' => $student['id'], 'from' => 'monitoring']) }}" variant="secondary" size="xs" class="uppercase font-bold tracking-wider !rounded-lg">Detail Hasil</x-button>
                    @if($student['status'] === 'blocked')
                    <x-button
                        wire:click="resumeAttempt({{ $student['id'] }})"
                        variant="primary" size="xs" class="uppercase font-bold tracking-wider !rounded-lg">Lanjutkan</x-button>
                    <x-button
                        @click="$dispatch('show-confirm-modal', [{
                            title: 'Akhiri Ujian?',
                            message: 'Siswa {{ $student['name'] }} sedang diblokir karena pelanggaran. Akhiri ujian sekarang?',
                            confirmText: 'Ya, Akhiri',
                            type: 'danger',
                            onConfirm: 'force-submit',
                            onConfirmDetail: {{ $student['id'] }}
                        }])"
                        variant="danger" size="xs" class="!bg-red-50 !text-red-600 !border-red-100 hover:!bg-red-100 uppercase font-bold tracking-wider !rounded-lg">Akhiri</x-button>
                    @elseif($student['status'] == 'working' || $student['status'] == 'in_progress')
                    <x-button 
                        @click="$dispatch('show-confirm-modal', [{ 
                            title: 'Akhiri Ujian?', 
                            message: 'Apakah Anda yakin ingin menghentikan ujian siswa ' + '{{ $student['name'] }}' + ' secara paksa? Tindakan ini tidak dapat dibatalkan.', 
                            confirmText: 'Ya, Akhiri', 
                            type: 'danger', 
                            onConfirm: 'force-submit',
                            onConfirmDetail: {{ $student['id'] }}
                        }])" 
                        variant="danger" size="xs" class="!bg-red-50 !text-red-600 !border-red-100 hover:!bg-red-100 uppercase font-bold tracking-wider !rounded-lg">Akhiri</x-button>
                    @else
                    <x-button variant="secondary" size="xs" disabled class="!text-gray-300 uppercase font-bold tracking-wider !rounded-lg">Lanjutkan</x-button>
                    <x-button variant="secondary" size="xs" disabled class="!text-gray-300 uppercase font-bold tracking-wider !rounded-lg">Akhiri</x-button>
                    @endif
                </x-monitor.student-card>
                @endforeach
            </div>
        </div>

        <!-- Live Activity Log Sidebar -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col sticky top-24 max-h-[500px]">
                <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-text-main flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                        Aktivitas Terbaru
                    </h3>
                    <span class="text-[10px] text-gray-400 font-mono">Live Update</span>
                </div>
                <div class="p-4 overflow-y-auto space-y-4 flex-1">
                    @forelse($live_logs as $log)
                    <div wire:key="log-{{ $log['id'] ?? $log['timestamp'] }}" 
                         wire:transition.slide.down
                         class="flex gap-3">
                        <div class="mt-1 flex-shrink-0 w-1.5 h-1.5 rounded-full
                            {{ $log['type'] === 'warning' ? 'bg-amber-500' : '' }}
                            {{ $log['type'] === 'success' ? 'bg-green-500' : '' }}
                            {{ $log['type'] === 'info' ? 'bg-blue-500' : '' }}
                            {{ $log['type'] === 'primary' ? 'bg-indigo-500' : '' }}
                            {{ $log['type'] === 'danger' ? 'bg-red-500' : '' }}
                        "></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-0.5">
                                <p class="text-[11px] font-bold text-gray-900 truncate">{{ $log['student'] }}</p>
                                <span class="text-[9px] font-mono text-gray-400 flex-shrink-0">{{ $log['time'] }}</span>
                            </div>
                            <p class="text-[10px] text-gray-500">{{ $log['activity'] }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-xs text-gray-400 py-4">Belum ada aktivitas.</div>
                    @endforelse
                </div>
                <div class="p-3 bg-gray-50 border-t border-gray-100 text-center">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Historical Logs available in Reports</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        console.log("🔊 Monitoring Detail Listener Initiated");
        
        if (typeof Echo !== 'undefined') {
            Echo.channel('security-monitoring')
                .listen('.student-violation', (e) => {
                    console.log("🔥 [FIRE] Detail Event Received:", e);
                });
        }
    });
</script>
