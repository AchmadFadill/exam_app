<div class="space-y-6">
    <x-header 
        title="LAPORAN HASIL UJIAN" 
        subtitle="Lihat statistik dan hasil ujian per kelas" 
    />

    <!-- Results List -->
    <x-table>
        <x-table.thead>
            <x-table.tr>
                <x-table.th>Nama Ujian</x-table.th>
                @if(request()->is('admin/*'))
                <x-table.th>Guru</x-table.th>
                @endif
                <x-table.th>Kelas</x-table.th>
                <x-table.th>Mata Pelajaran</x-table.th>
                <x-table.th>Tanggal</x-table.th>
                <x-table.th class="text-center">Peserta</x-table.th>
                <x-table.th class="text-center">Rata-rata</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </x-table.tr>
        </x-table.thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($results as $result)
            <x-table.tr>
                <x-table.td>
                    <span class="font-black text-text-main uppercase tracking-tight group-hover:text-primary transition-colors">{{ $result['exam_name'] }}</span>
                </x-table.td>
                @if(request()->is('admin/*'))
                <x-table.td class="font-bold text-text-muted">{{ $result['teacher_name'] }}</x-table.td>
                @endif
                <x-table.td class="italic font-bold text-text-muted">{{ $result['class'] }}</x-table.td>
                <x-table.td class="font-bold text-text-muted">{{ $result['subject'] }}</x-table.td>
                <x-table.td class="text-[10px] font-black text-text-muted uppercase tracking-widest italic">{{ $result['date'] }}</x-table.td>
                <x-table.td class="text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black bg-blue-500/10 text-primary border border-primary/20 uppercase tracking-widest">
                        {{ $result['participants'] }} Siswa
                    </span>
                </x-table.td>
                <x-table.td class="text-center">
                    <span class="text-lg font-black {{ $result['avg_score'] >= 75 ? 'text-green-600' : 'text-amber-600' }}">
                        {{ $result['avg_score'] }}
                    </span>
                </x-table.td>
                <x-table.td class="text-right">
                    <div class="flex justify-end">
                        <x-button variant="soft" href="{{ route($detailRoute, $result['id']) }}" class="px-6 text-[10px]">Detail</x-button>
                    </div>
                </x-table.td>
            </x-table.tr>
            @empty
            <x-empty-state 
                colspan="{{ request()->is('admin/*') ? 8 : 7 }}" 
                title="Hasil ujian kosong" 
                message="Belum ada hasil ujian yang dapat ditampilkan saat ini." 
                icon="folder-open" 
            />
            @endforelse
        </tbody>
    </x-table>
</div>
