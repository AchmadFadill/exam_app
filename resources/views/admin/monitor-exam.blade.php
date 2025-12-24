@section('title', 'Monitoring Ujian')
<div>
    <x-slot name="title">Monitoring Ujian Live (Admin)</x-slot>
    
    <div wire:poll.10s class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="#" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-text-main">Monitoring Ujian</h2>
                <p class="text-text-muted text-sm">Ujian Harian Matematika </p>
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

        <!-- Student Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($students as $student)
            <x-monitor.student-card :student="$student">
                <button class="px-2 py-1 text-xs border border-gray-200 rounded text-text-main hover:bg-gray-50">Detail</button>
                @if($student['status'] == 'working')
                <button wire:click="forceSubmit('mock_id')" class="px-2 py-1 text-xs bg-red-50 text-red-600 rounded hover:bg-red-100">Akhiri</button>
                @else
                <button class="px-2 py-1 text-xs border border-gray-200 rounded text-gray-300 cursor-not-allowed">Akhiri</button>
                @endif
            </x-monitor.student-card>
            @endforeach
        </div>
    </div>
</div>
