@section('title', 'Monitoring Ujian')

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route($backRoute) }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-text-main">{{ $exam->name }}</h2>
                <p class="text-text-muted text-sm">{{ $exam->subject->name }} • {{ $exam->classrooms->pluck('name')->join(', ') }}</p>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- Token Card -->
        @if($exam->token)
        <div x-data="{ copied: false }" 
             @click="navigator.clipboard.writeText('{{ $exam->token }}'); copied = true; setTimeout(() => copied = false, 2000)" 
             class="bg-gradient-to-br from-indigo-600 to-primary rounded-xl p-4 text-white shadow-lg shadow-primary/20 flex flex-col justify-center items-center relative overflow-hidden group cursor-pointer hover:scale-[1.02] transition-all active:scale-95">
            <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            
            <!-- Default State -->
            <div x-show="!copied" class="flex flex-col items-center">
                <div class="text-[10px] uppercase tracking-widest font-bold opacity-80 mb-1 flex items-center gap-1">
                    Token Ujian
                    <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                </div>
                <div class="font-mono text-3xl font-black tracking-wider group-hover:scale-110 transition-transform">{{ $exam->token }}</div>
            </div>

            <!-- Copied State -->
            <div x-show="copied" x-cloak class="flex flex-col items-center animate-pulse">
                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <div class="text-xs font-black uppercase tracking-widest">Tersalin!</div>
            </div>
        </div>
        @else
        <x-monitor.summary-card label="Token" value="-" variant="gray" />
        @endif

        <x-monitor.summary-card label="Total Siswa" :value="$exam->classrooms->sum('students_count')" />
        <x-monitor.summary-card label="Sedang Mengerjakan" :value="$students->where('status', 'in_progress')->count()" variant="primary" />
        <x-monitor.summary-card label="Selesai" :value="$students->whereIn('status', ['completed', 'graded', 'submitted'])->count()" variant="success" />
        <x-monitor.summary-card label="Belum Mulai" :value="$students->where('status', 'not_started')->count()" variant="gray" />
    </div>

    <!-- Filter Bar -->
    <x-monitor.filter-bar />

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Student Grid -->
        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($students as $student)
                <x-monitor.student-card :student="$student">
                    <x-button href="{{ route($studentDetailRoute, [$exam->id, $student['id']]) }}" variant="secondary" size="xs" class="uppercase font-bold tracking-wider !rounded-lg">Detail</x-button>
                    @if($student['status'] == 'working' || $student['status'] == 'in_progress')
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
                    <x-button variant="secondary" size="xs" disabled class="!text-gray-300 uppercase font-bold tracking-wider !rounded-lg">Akhiri</x-button>
                    @endif
                </x-monitor.student-card>
                @endforeach
            </div>
        </div>

        <!-- Live Activity Log Sidebar -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col h-full max-h-[1000px]">
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
