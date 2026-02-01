@section('title', 'Penilaian Essay')

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-bold text-2xl text-text-main">Penilaian Essay</h2>
            <p class="text-text-muted text-sm">Daftar ujian yang memerlukan koreksi manual</p>
        </div>
    </div>

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
        <tbody class="bg-bg-surface divide-y divide-gray-200">
            @foreach($exams as $exam)
            <x-table.tr>
                <x-table.td>
                    <div class="text-sm font-medium text-text-main">{{ $exam['name'] }}</div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                    {{ $exam['class'] }}
                </x-table.td>
                 <x-table.td class="whitespace-nowrap italic font-bold">
                    {{ $exam['date'] }}
                </x-table.td>
                <x-table.td class="whitespace-nowrap space-y-1">
                    @if($exam['pending_count'] > 0)
                        <span class="px-2.5 py-0.5 inline-flex text-[10px] font-black uppercase tracking-widest rounded-full bg-amber-100 text-amber-800">
                            Butuh Koreksi ({{ $exam['pending_count'] }})
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 inline-flex text-[10px] font-black uppercase tracking-widest rounded-full bg-green-100 text-green-800">
                            Selesai Dinilai
                        </span>
                    @endif
                    <br>
                    @if($exam['is_published'] ?? false)
                        <span class="px-2.5 py-0.5 inline-flex text-[9px] font-black uppercase tracking-widest rounded-full bg-blue-100 text-blue-800">
                            Sudah Terbit
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 inline-flex text-[9px] font-black uppercase tracking-widest rounded-full bg-slate-100 text-slate-800">
                            Draft
                        </span>
                    @endif
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-right font-medium">
                    <div class="flex justify-end gap-3">
                        @if(!($exam['is_published'] ?? false) && $exam['pending_count'] == 0)
                            <x-button variant="soft" class="bg-blue-50 text-blue-600 hover:bg-blue-100">
                                Publish Nilai
                            </x-button>
                        @endif

                        @if($exam['pending_count'] > 0)
                            <x-button href="{{ route('teacher.grading.show', ['exam' => $exam['id']]) }}" variant="soft">Koreksi</x-button>
                        @else
                            <x-button href="{{ route('teacher.grading.show', ['exam' => $exam['id']]) }}" variant="secondary" class="border-transparent bg-transparent hover:bg-transparent hover:text-primary shadow-none px-2 uppercase text-[10px] tracking-widest">Detail</x-button>
                        @endif
                    </div>
                </x-table.td>
            </x-table.tr>
            @endforeach
        </tbody>
    </x-table>
</div>

