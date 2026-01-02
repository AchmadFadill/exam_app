@section('title', 'Monitoring Ujian')

<div wire:poll.10s class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route($backRoute) }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-text-main">Monitoring Ujian</h2>
                <p class="text-text-muted text-sm">Ujian Harian Matematika</p>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-monitor.summary-card label="Total Siswa" value="32" />
        <x-monitor.summary-card label="Sedang Mengerjakan" value="25" variant="primary" />
        <x-monitor.summary-card label="Selesai" value="6" variant="success" />
        <x-monitor.summary-card label="Belum Mulai" value="1" variant="gray" />
    </div>

    <!-- Filter Bar -->
    <x-monitor.filter-bar />

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Student Grid -->
        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($students as $student)
                <x-monitor.student-card :student="$student">
                    <button class="px-2 py-1 text-xs border border-gray-200 rounded text-text-main hover:bg-gray-50 uppercase font-bold tracking-wider">Detail</button>
                    @if($student['status'] == 'working')
                    <button wire:click="forceSubmit('mock_id')" class="px-2 py-1 text-xs bg-red-50 text-red-600 rounded hover:bg-red-100 uppercase font-bold tracking-wider">Akhiri</button>
                    @else
                    <button class="px-2 py-1 text-xs border border-gray-200 rounded text-gray-300 cursor-not-allowed uppercase font-bold tracking-wider">Akhiri</button>
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
                    @foreach($live_logs as $log)
                    <div class="flex gap-3">
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
                    @endforeach
                </div>
                <div class="p-3 bg-gray-50 border-t border-gray-100 text-center">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Historical Logs available in Reports</span>
                </div>
            </div>
        </div>
    </div>
</div>
