@section('title', 'Penilaian Essay')

<div class="space-y-6">
    <x-header 
        title="Penilaian Essay" 
        subtitle="Daftar ujian yang memerlukan koreksi manual" 
    />

    <x-table>
        <x-table.thead>
            <x-table.tr>
                <x-table.th>Nama Ujian</x-table.th>
                <x-table.th>Kelas</x-table.th>
                <x-table.th>Tanggal</x-table.th>
                <x-table.th>Status</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </x-table.tr>
        </x-table.thead>
        <tbody class="bg-bg-surface divide-y divide-border-subtle dark:divide-slate-800">
            @forelse($exams as $exam)
            <x-table.tr>
                <x-table.td>
                    <div class="text-sm font-black text-text-main line-clamp-1" title="{{ $exam->name }}">{{ $exam->name }}</div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                    <span class="text-xs font-bold text-text-muted">{{ $exam->class }}</span>
                </x-table.td>
                 <x-table.td class="whitespace-nowrap italic font-bold text-text-muted text-xs">
                    {{ date('d M Y', strtotime($exam->date)) }}
                </x-table.td>
                <x-table.td class="whitespace-nowrap space-y-1">
                    @if($exam->pending_count > 0)
                        <span class="px-3 py-1 inline-flex text-[10px] font-black uppercase tracking-widest rounded-full bg-amber-100 text-amber-700 ring-1 ring-amber-500/20">
                            Butuh Koreksi ({{ $exam->pending_count }})
                        </span>
                    @else
                        <span class="px-3 py-1 inline-flex text-[10px] font-black uppercase tracking-widest rounded-full bg-green-100 text-green-700 ring-1 ring-green-500/20">
                            Selesai Dinilai
                        </span>
                    @endif
                    <br>
                    @if($exam->is_published)
                        <span class="px-2.5 py-0.5 inline-flex text-[9px] font-black uppercase tracking-widest rounded-full bg-blue-100 text-blue-700 mt-1 opacity-80">
                            Sudah Terbit
                        </span>
                    @endif
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-right font-medium">
                    <div class="flex justify-end gap-3">
                        @if($exam->pending_count > 0)
                            <x-button href="{{ route('teacher.grading.show', ['exam' => $exam->id]) }}" variant="primary" class="shadow-lg shadow-primary/20">
                                Koreksi ({{ $exam->pending_count }})
                            </x-button>
                        @else
                            <x-button href="{{ route('teacher.grading.show', ['exam' => $exam->id]) }}" variant="secondary" class="hover:bg-gray-100 dark:hover:bg-slate-800">
                                LIHAT DETAIL
                            </x-button>
                        @endif
                    </div>
                </x-table.td>
            </x-table.tr>
            @empty
            <x-table.tr>
                <x-table.td colspan="5" class="py-20 text-center text-text-muted italic font-bold">
                    Tidak ada ujian yang memerlukan koreksi manual saat ini. <br>
                    <span class="text-primary not-italic mt-2 block">Pekerjaan selesai! ☕</span>
                </x-table.td>
            </x-table.tr>
            @endforelse
        </tbody>
    </x-table>
    
    <div class="mt-4">
        {{ $exams->links() }}
    </div>
</div>

