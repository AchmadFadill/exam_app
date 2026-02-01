@section('title', 'Daftar Siswa - ' . $examName)

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('teacher.grading.index') }}" class="p-2 rounded-full hover:bg-gray-100 text-text-muted transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div class="flex-1">
            <h2 class="font-bold text-2xl text-text-main">{{ $examName }}</h2>
            <p class="text-text-muted text-sm">{{ $className }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if($isPublished)
                <span class="inline-flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-xl text-sm font-bold border border-green-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                    Sudah Dipublikasikan
                </span>
            @else
                <button wire:click="publish" class="inline-flex items-center gap-2 bg-primary hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-lg shadow-blue-200 active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    Terbitkan Nilai (Publish)
                </button>
            @endif
        </div>
    </div>

    <x-table>
        <x-table.thead>
            <x-table.tr>
                <x-table.th>Nama Siswa</x-table.th>
                <x-table.th>Waktu Submit</x-table.th>
                <x-table.th>Status</x-table.th>
                <x-table.th>Nilai</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </x-table.tr>
        </x-table.thead>
        <tbody class="bg-bg-surface divide-y divide-gray-200">
            @foreach($students as $student)
            <x-table.tr>
                <x-table.td>
                    <div class="text-sm font-black text-text-main tracking-tight uppercase group-hover:text-primary transition-colors">{{ $student['name'] }}</div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap italic font-bold text-text-muted uppercase text-[10px] tracking-widest">
                    {{ $student['submitted_at'] }}
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                    @if($student['status'] == 'Sudah Dinilai')
                        <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full bg-green-500/10 text-green-600 border border-green-500/20">
                            {{ $student['status'] }}
                        </span>
                    @else
                        <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full bg-amber-500/10 text-amber-600 border border-amber-500/20">
                            {{ $student['status'] }}
                        </span>
                    @endif
                </x-table.td>
                 <x-table.td class="whitespace-nowrap text-sm text-text-main font-black">
                    {{ $student['score'] }}
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-right font-medium">
                    <x-button href="{{ route('teacher.grading.detail', ['exam' => $examId, 'student' => $student['id']]) }}" variant="soft" class="text-[10px] px-6">
                        {{ $student['status'] == 'Sudah Dinilai' ? 'Edit Nilai' : 'Beri Nilai' }}
                    </x-button>
                </x-table.td>
            </x-table.tr>
            @endforeach
        </tbody>
    </x-table>
</div>
