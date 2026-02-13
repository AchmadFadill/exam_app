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
            @else
                <x-button wire:click="publish" variant="primary" class="px-4 py-2.5 text-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    TERBITKAN NILAI
                </x-button>
            @endif
        </div>
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
                    <x-button href="{{ auth()->user()->isAdmin() ? route('admin.grading.detail', ['exam' => $examId, 'student' => $attempt->student_id]) : route('teacher.grading.detail', ['exam' => $examId, 'student' => $attempt->student_id]) }}"
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
