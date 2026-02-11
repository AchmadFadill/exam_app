<div class="space-y-4 sm:space-y-6">
    <x-header 
        title="Riwayat Hasil Ujian" 
        subtitle="Pantau perkembangan belajarmu di sini." 
    />

    <!-- Results History Table -->
    <x-table>
        <x-table.thead>
            <x-table.tr>
                <x-table.th>Mata Pelajaran</x-table.th>
                <x-table.th>Nama Ujian</x-table.th>
                <x-table.th>Tanggal Selesai</x-table.th>
                <x-table.th>Nilai</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </x-table.tr>
        </x-table.thead>
        <tbody class="bg-bg-surface dark:bg-bg-surface divide-y divide-border-subtle dark:divide-border-subtle">
            @forelse($results as $result)
            <x-table.tr>
                <x-table.td class="whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="p-1.5 sm:p-2 rounded-xl bg-primary/10 text-primary mr-3 sm:mr-4 shadow-inner shrink-0">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div class="text-xs sm:text-sm font-black text-text-main uppercase tracking-tight truncate max-w-[120px] sm:max-w-none">{{ $result['subject'] }}</div>
                    </div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap italic text-text-muted font-bold text-xs sm:text-sm">
                    {{ $result['exam_name'] }}
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-[9px] sm:text-[10px] font-black text-text-muted uppercase tracking-widest">
                    {{ $result['submitted_at'] }}
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                    @if(($result['status'] === 'graded' || $result['passed'] !== null) && $result['show_score_to_student'])
                        <span class="text-lg sm:text-xl font-black {{ $result['passed'] ? 'text-green-600' : 'text-red-600' }}">{{ number_format($result['score'], 1) }}</span>
                    @elseif($result['status'] === 'graded' || $result['passed'] !== null)
                        <span class="px-2 sm:px-3 py-1 text-[8px] sm:text-[10px] font-black uppercase tracking-widest rounded-full bg-gray-500/10 text-gray-600 italic border border-gray-500/20">
                            Disembunyikan
                        </span>
                    @else
                        <span class="px-2 sm:px-3 py-1 text-[8px] sm:text-[10px] font-black uppercase tracking-widest rounded-full bg-amber-500/10 text-amber-600 italic border border-amber-500/20">
                            Belum Terbit
                        </span>
                    @endif
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-right">
                    @if(($result['status'] === 'graded' || $result['passed'] !== null) && $result['show_answers_to_student'])
                        <x-button href="{{ route('student.results.detail', ['id' => $result['id']]) }}" variant="soft" class="text-[10px] px-6">Lihat Detail</x-button>
                    @elseif($result['status'] === 'graded' || $result['passed'] !== null)
                        <span class="text-[10px] font-black uppercase tracking-widest text-text-muted opacity-60">Nonaktif</span>
                    @else
                        <span class="text-[10px] font-black uppercase tracking-widest text-text-muted opacity-40">Tunggu</span>
                    @endif
                </x-table.td>
            </x-table.tr>
            @empty
            <x-empty-state 
                colspan="5" 
                title="Hasil Belum Tersedia" 
                message="Selesaikan ujian untuk melihat riwayat hasil dan analisis nilai Anda." 
                icon="folder-open" 
            />
            @endforelse
        </tbody>
    </x-table>

    <!-- Pagination -->
    @if($results->hasPages())
    <div class="mt-6">
        {{ $results->links() }}
    </div>
    @endif
</div>
