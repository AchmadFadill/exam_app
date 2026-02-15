@section('title', 'Laporan Hasil Ujian')

<div class="space-y-8">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6 sm:gap-4">
        <div class="flex items-center gap-3 sm:gap-4">
            <x-button href="{{ route($backRoute) }}" variant="secondary" size="sm" square="true" class="!rounded-xl group">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </x-button>
            <div class="min-w-0">
                <h2 class="font-black text-xl sm:text-2xl text-text-main leading-tight truncate uppercase tracking-tight italic">Detail Laporan</h2>
                <div class="flex items-center gap-2 mt-0.5">
                    <p class="text-[10px] sm:text-sm font-bold text-primary truncate max-w-[150px] sm:max-w-none">{{ $exam['exam_name'] }}</p>
                    <span class="text-gray-300">•</span>
                    <p class="text-[10px] sm:text-sm font-black text-text-muted truncate max-w-[150px] sm:max-w-none uppercase tracking-widest italic">{{ $exam['class'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <x-card variant="stat" title="Rata-rata Nilai" :value="$exam['avg_score']" color="primary">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </x-slot>
        </x-card>
        <x-card variant="stat" title="Nilai Tertinggi" :value="$exam['highest']" color="green">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </x-slot>
        </x-card>
        <x-card variant="stat" title="Nilai Terendah" :value="$exam['lowest']" color="amber">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
            </x-slot>
        </x-card>
        <x-card variant="stat" title="Total Peserta" :value="$exam['participants']" color="indigo">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </x-slot>
        </x-card>
    </div>

    <div class="space-y-6">
    <!-- Most Failed Questions -->
    <x-card title="Analisis Soal">
        <p class="text-xs text-text-muted mb-4 uppercase tracking-widest font-bold">Soal Paling Banyak Salah</p>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @forelse($most_failed_questions as $q)
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 group hover:border-red-100 transition-colors h-full">
                <div class="flex justify-between items-start mb-2 gap-2">
                    <span class="px-2.5 h-8 rounded-lg bg-white border border-gray-200 inline-flex items-center justify-center font-bold text-sm text-gray-900 whitespace-nowrap">Soal {{ $q['number'] }}</span>
                    <span class="text-xs font-black text-red-600 uppercase tracking-widest text-right">{{ $q['failed_percentage'] }}% Gagal</span>
                </div>
                <p class="text-xs text-gray-600 line-clamp-3 leading-relaxed mb-3">{{ $q['text'] }}</p>
                <div class="pt-3 border-t border-gray-200/50 text-[10px] text-gray-500">
                     Jawaban Benar: <span class="font-bold text-green-600">{{ $q['correct_answer'] }}</span>
                </div>
            </div>
            @empty
            <div class="col-span-full py-8">
                <x-empty-state 
                    title="Sempurna!" 
                    message="Semua soal dijawab dengan baik oleh seluruh peserta. ✨" 
                    icon="coffee" 
                />
            </div>
            @endforelse
        </div>
        <x-button variant="primary" href="{{ route($analysisRoute, $exam['id']) }}" class="w-full sm:w-auto mt-6">Lihat Analisis Lengkap</x-button>
    </x-card>

    <!-- Student List -->
    <x-card title="Hasil Per Siswa">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                <label for="classroomFilter" class="text-[10px] font-black text-text-muted uppercase tracking-widest">Filter Kelas</label>
                <select id="classroomFilter" wire:model.live="classroomFilter" class="w-full sm:w-auto px-3 py-2 border border-border-main rounded-xl bg-white text-xs font-bold text-text-main focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($assigned_classes as $class)
                        <option value="{{ $class['id'] }}">{{ $class['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <x-button href="{{ route($printRoute, ['id' => $exam['id']]) }}" variant="soft" class="text-[10px] sm:text-xs uppercase tracking-widest w-full sm:w-auto">
                Cetak Rekap
            </x-button>
        </div>

        <!-- Filter Buttons -->
        <div class="flex flex-wrap gap-2 mb-6 -mt-2">
            <x-button
                wire:click="sortByHighest"
                variant="{{ $sortBy === 'highest' ? 'success' : 'secondary' }}"
                size="sm"
                class="!rounded-xl uppercase tracking-widest {{ $sortBy !== 'highest' ? 'opacity-60 grayscale-[0.5]' : '' }}">
                Tertinggi
            </x-button>
            <x-button
                wire:click="sortByLowest"
                variant="{{ $sortBy === 'lowest' ? 'warning' : 'secondary' }}"
                size="sm"
                class="!rounded-xl uppercase tracking-widest {{ $sortBy !== 'lowest' ? 'opacity-60 grayscale-[0.5]' : '' }}">
                Terendah
            </x-button>
            <x-button
                wire:click="sortByFastest"
                variant="{{ $sortBy === 'fastest' ? 'primary' : 'secondary' }}"
                size="sm"
                class="!rounded-xl uppercase tracking-widest {{ $sortBy !== 'fastest' ? 'opacity-60 grayscale-[0.5]' : '' }}">
                Tercepat
            </x-button>
            <x-button
                wire:click="sortBySlowest"
                variant="{{ $sortBy === 'slowest' ? 'primary' : 'secondary' }}"
                size="sm"
                class="!rounded-xl uppercase tracking-widest {{ $sortBy === 'slowest' ? '!bg-purple-500 shadow-purple-500/20 shadow-lg' : 'opacity-60 grayscale-[0.5]' }}">
                Terlambat
            </x-button>
            @if($sortBy !== 'default')
                <x-button
                    wire:click="resetFilter"
                    variant="secondary"
                    size="sm"
                    class="!rounded-xl uppercase tracking-widest !bg-red-50 !border-red-500/10 !text-red-600 hover:!bg-red-500/10 active:!bg-red-500/20">
                    Nonaktifkan Filter
                </x-button>
            @endif
        </div>
        <x-table>
            <x-table.thead>
                <x-table.tr>
                    <x-table.th>Nama Siswa</x-table.th>
                    <x-table.th class="text-center">Nilai</x-table.th>
                    <x-table.th class="text-center">Status</x-table.th>
                    <x-table.th class="text-right">Aksi</x-table.th>
                </x-table.tr>
            </x-table.thead>
            <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                @forelse($students as $student)
                <x-table.tr>
                    <x-table.td>
                        <div class="font-black text-text-main uppercase tracking-tight">{{ $student['name'] }}</div>
                        <div class="text-[10px] text-text-muted font-bold uppercase tracking-widest mt-0.5">{{ $student['started_at'] }} - {{ $student['submitted_at'] }}</div>
                    </x-table.td>
                    <x-table.td class="text-center font-black text-xl text-text-main italic">{{ $student['score'] }}</x-table.td>
                    <x-table.td class="text-center">
                        <span class="inline-flex items-center whitespace-nowrap px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border
                            {{ $student['status'] == 'Lulus' ? 'bg-green-500/10 text-green-600 border-green-500/20' : ($student['status'] == 'Pending Penilaian' ? 'bg-amber-500/10 text-amber-700 border-amber-500/20' : ($student['status'] == 'Belum Mengerjakan' ? 'bg-gray-500/10 text-gray-600 border-gray-500/20' : 'bg-red-500/10 text-red-600 border-red-500/20')) }}">
                            {{ $student['status'] }}
                        </span>
                    </x-table.td>
                    <x-table.td class="text-right">
                        <div class="flex justify-end">
                            <x-button variant="soft" href="{{ route($studentDetailRoute, ['examId' => $exam['id'], 'studentId' => $student['id'], 'from' => 'report']) }}" class="px-6 text-[10px]">Detail</x-button>
                        </div>
                    </x-table.td>
                </x-table.tr>
                @empty
                <x-empty-state 
                    colspan="4" 
                    title="Tidak Ada Peserta" 
                    message="Belum ada data peserta yang menyelesaikan ujian ini." 
                    icon="coffee" 
                />
                @endforelse
            </tbody>
        </x-table>
        @if(method_exists($students, 'hasPages') && $students->hasPages())
            <div class="mt-4 border border-border-main rounded-xl px-4 py-3 bg-bg-surface">
                <div class="flex items-center justify-between gap-3">
                    <button
                        type="button"
                        wire:click="previousPage('studentsPage')"
                        @disabled($students->onFirstPage())
                        class="h-8 w-8 rounded-lg border border-border-main bg-white text-text-muted hover:text-text-main hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center transition-all"
                        aria-label="Halaman sebelumnya"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>

                    <p class="text-[11px] font-bold text-text-muted">
                        Hal {{ $students->currentPage() }} dari {{ $students->lastPage() }}
                    </p>

                    <button
                        type="button"
                        wire:click="nextPage('studentsPage')"
                        @disabled(!$students->hasMorePages())
                        class="h-8 w-8 rounded-lg border border-border-main bg-white text-text-muted hover:text-text-main hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center transition-all"
                        aria-label="Halaman berikutnya"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </x-card>
</div>
</div>
