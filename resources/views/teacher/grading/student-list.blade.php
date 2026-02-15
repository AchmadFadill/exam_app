@section('title', 'Daftar Siswa - ' . $examName)

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <x-button href="{{ auth()->user()->isAdmin() ? route('admin.grading.index') : route('teacher.grading.index') }}" variant="secondary" size="sm" square="true" class="!rounded-xl group">
            <svg class="w-6 h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </x-button>
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
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="relative sm:col-span-2">
            <svg class="w-4 h-4 text-text-muted absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="Cari nama siswa atau NIS..."
                class="w-full pl-11 pr-4 py-3 bg-bg-surface border border-border-main rounded-xl text-sm font-medium text-text-main placeholder:text-text-muted/70 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all"
            >
        </div>
        <select
            wire:model.live="classroomFilter"
            class="w-full px-4 py-3 bg-bg-surface border border-border-main rounded-xl text-sm font-medium text-text-main focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all"
        >
            <option value="">Semua Kelas</option>
            @foreach($classrooms as $classroom)
                <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
            @endforeach
        </select>
    </div>

    <x-table>
        <x-table.thead>
            <x-table.tr>
                <x-table.th>Nama Siswa</x-table.th>
                <x-table.th>Waktu Submit</x-table.th>
                <x-table.th>Status</x-table.th>
                <x-table.th>Nilai Akhir</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </x-table.tr>
        </x-table.thead>
        <tbody class="bg-bg-surface divide-y divide-border-subtle dark:divide-slate-800">
            @forelse($attempts as $attempt)
            @php
                $attemptStatus = $attempt->status instanceof \App\Enums\ExamAttemptStatus ? $attempt->status->value : $attempt->status;
            @endphp
            <x-table.tr>
                <x-table.td>
                    <div class="text-sm font-black text-text-main tracking-tight uppercase group-hover:text-primary transition-colors">
                        {{ $attempt->student->user->name ?? 'Siswa Tidak Dikenal' }}
                    </div>
                </x-table.td>
                <x-table.td class="whitespace-nowrap italic font-bold text-text-muted uppercase text-[10px] tracking-widest">
                    {{ $attempt->submitted_at ? $attempt->submitted_at->format('d M Y H:i') : '-' }}
                </x-table.td>
                <x-table.td class="whitespace-nowrap">
                    @if($attemptStatus == \App\Enums\ExamAttemptStatus::Graded->value)
                        <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full bg-green-100 text-green-700 ring-1 ring-green-500/20">
                            Selesai Dinilai
                        </span>
                    @else
                        <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full bg-amber-100 text-amber-700 ring-1 ring-amber-500/20">
                            Butuh Koreksi
                        </span>
                    @endif
                </x-table.td>
                 <x-table.td class="whitespace-nowrap text-sm text-text-main font-black">
                    @if($attemptStatus == \App\Enums\ExamAttemptStatus::Graded->value)
                        {{ number_format($attempt->total_score, 1) }}
                    @else
                        <span class="text-text-muted text-xs italic">Pending</span>
                    @endif
                </x-table.td>
                <x-table.td class="whitespace-nowrap text-right font-medium">
                    <x-button href="{{ auth()->user()->isAdmin() ? route('admin.grading.detail', ['exam' => $examId, 'student' => $attempt->student_id, 'classroomFilter' => $classroomFilter, 'gradingPage' => $attempts->currentPage()]) : route('teacher.grading.detail', ['exam' => $examId, 'student' => $attempt->student_id, 'classroomFilter' => $classroomFilter, 'gradingPage' => $attempts->currentPage()]) }}"
                              variant="{{ $attemptStatus == \App\Enums\ExamAttemptStatus::Graded->value ? 'secondary' : 'primary' }}"
                              class="text-[10px] px-6">
                        {{ $attemptStatus == \App\Enums\ExamAttemptStatus::Graded->value ? 'EDIT NILAI' : 'BERI NILAI' }}
                    </x-button>
                </x-table.td>
            </x-table.tr>
            @empty
            <x-empty-state 
                colspan="5" 
                title="Menunggu Pengumpulan" 
                message="Belum ada siswa yang mengumpulkan ujian untuk sesi ini." 
                icon="coffee" 
            />
            @endforelse
        </tbody>
    </x-table>
    
    <div class="mt-4">
        {{ $attempts->links() }}
    </div>
</div>
