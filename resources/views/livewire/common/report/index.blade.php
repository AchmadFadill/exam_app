<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 uppercase">Laporan Hasil Ujian</h1>
            <p class="text-sm text-gray-500">Lihat statistik dan hasil ujian per kelas</p>
        </div>
    </div>

    <!-- Results List -->
    <x-table>
        <x-table.thead>
            <x-table.tr>
                <x-table.th>Nama Ujian</x-table.th>
                <x-table.th>Kelas</x-table.th>
                <x-table.th>Mata Pelajaran</x-table.th>
                <x-table.th>Tanggal</x-table.th>
                <x-table.th class="text-center">Peserta</x-table.th>
                <x-table.th class="text-center">Rata-rata</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </x-table.tr>
        </x-table.thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($results as $result)
            <x-table.tr>
                <x-table.td>
                    <span class="font-black text-text-main uppercase tracking-tight group-hover:text-primary transition-colors">{{ $result['exam_name'] }}</span>
                </x-table.td>
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
            @endforeach
        </tbody>
    </x-table>
</div>
