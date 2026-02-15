@props([
    'exams' => [],
    'emptyTitle' => 'Tidak Ada Ujian Aktif',
    'emptyMessage' => 'Sedang tidak ada ujian yang dilaksanakan saat ini.',
])

<div class="bg-bg-surface dark:bg-bg-surface rounded-[2rem] shadow-xl shadow-black/5 border border-border-main dark:border-border-main divide-y divide-border-subtle dark:divide-border-subtle overflow-hidden">
    @forelse($exams as $exam)
        @php
            $statusLabel = match ($exam['status'] ?? 'scheduled') {
                'scheduled' => 'Dijadwalkan',
                'active', 'running', 'ongoing' => 'Berlangsung',
                'in_progress' => 'Sedang Mengerjakan',
                'submitted' => 'Dikumpulkan',
                'graded' => 'Dinilai',
                'completed' => 'Selesai',
                'timed_out' => 'Waktu Habis',
                default => ucfirst(str_replace('_', ' ', (string) ($exam['status'] ?? '-'))),
            };
        @endphp

        <div class="p-8 hover:bg-gray-50/50 dark:hover:bg-slate-800/30 transition-colors">
            <div class="flex justify-between items-start mb-6 gap-6">
                <div class="min-w-0">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="px-3 py-1 bg-gray-100 dark:bg-slate-800 text-text-muted rounded-lg text-[10px] font-black uppercase tracking-widest">
                            {{ $exam['class'] }}
                        </span>
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg text-[10px] font-black uppercase tracking-widest">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <h4 class="text-xl font-black text-text-main tracking-tight">{{ $exam['subject'] }}</h4>
                    @if(!empty($exam['name']))
                        <p class="text-sm text-text-muted mt-1 font-semibold truncate">{{ $exam['name'] }}</p>
                    @endif
                    @if(!empty($exam['teacher']))
                        <p class="text-sm text-text-muted mt-1 font-medium">Guru: {{ $exam['teacher'] }}</p>
                    @endif
                    @if(!empty($exam['start_time']) && !empty($exam['end_time']))
                        <p class="text-xs text-text-muted mt-2 font-bold uppercase tracking-wider opacity-70">
                            {{ $exam['start_time'] }} - {{ $exam['end_time'] }}
                        </p>
                    @endif
                </div>

                <div class="text-right flex-shrink-0">
                    <span class="text-3xl font-black text-text-main tracking-tighter">{{ (int) ($exam['progress'] ?? 0) }}%</span>
                    <p class="text-[10px] text-text-muted font-black uppercase tracking-widest opacity-60">Progres</p>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <div class="flex-1 bg-gray-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden shadow-inner">
                    <div class="bg-primary h-full transition-all duration-1000 ease-out shadow-[0_0_12px_rgba(30,64,175,0.4)]" style="width: {{ (int) ($exam['progress'] ?? 0) }}%"></div>
                </div>
                <span class="text-xs font-black text-text-main whitespace-nowrap" title="Siswa dengan status sedang mengerjakan (in_progress)">
                    {{ (int) ($exam['students_online'] ?? 0) }}/{{ (int) ($exam['total_students'] ?? 0) }}
                    <span class="text-green-500">Aktif</span>
                </span>
                <a href="{{ $exam['monitor_url'] ?? '#' }}" class="px-6 py-3 bg-primary text-white font-black rounded-2xl hover:bg-blue-700 transition-all text-xs uppercase tracking-widest shadow-xl shadow-primary/20 flex items-center gap-2">
                    Monitor
                </a>
            </div>
        </div>
    @empty
        <div class="p-8">
            <x-empty-state :title="$emptyTitle" :message="$emptyMessage" icon="coffee" />
        </div>
    @endforelse
</div>
