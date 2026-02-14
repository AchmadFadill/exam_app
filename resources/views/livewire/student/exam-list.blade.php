<div class="space-y-6">
    <!-- Header & Filters -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Daftar Ujian</h1>
                <p class="text-gray-500 mt-1">Kelola dan pantau jadwal ujianmu di sini</p>
            </div>
            
            <div class="flex p-1 bg-gray-100 rounded-xl overflow-x-auto">
                @foreach([
                    'all' => 'Semua',
                    'active' => 'Berlangsung', 
                    'upcoming' => 'Akan Datang',
                    'history' => 'Riwayat'
                ] as $key => $label)
                <button wire:click="setFilter('{{ $key }}')" 
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-all whitespace-nowrap active:scale-95 {{ $filter === $key ? 'bg-white text-primary shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Exam List -->
    <div class="grid gap-4">
        @forelse($exams as $exam)
            @php 
                $status = $this->getExamStatus($exam);
                $statusColor = match($status) {
                    'active' => 'blue',
                    'in_progress' => 'yellow',
                    'upcoming' => 'gray',
                    'submitted' => 'green',
                    'missed' => 'red',
                    default => 'gray'
                };
                
                $statusLabel = match($status) {
                    'active' => 'Berlangsung',
                    'in_progress' => 'Sedang Dikerjakan',
                    'upcoming' => 'Akan Datang',
                    'submitted' => 'Selesai',
                    'missed' => 'Terlewat',
                    default => 'Unknown'
                };
            @endphp
            
            <div class="group bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 hover:border-gray-200 hover:shadow-md transition-all">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 sm:gap-6">
                    <!-- Left: Info -->
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-{{ $statusColor }}-50 flex items-center justify-center text-{{ $statusColor }}-600 shrink-0">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-medium bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 border border-{{ $statusColor }}-100">
                                    {{ $statusLabel }}
                                </span>
                                <span class="text-[10px] sm:text-xs text-gray-500 truncate max-w-[150px]">{{ $exam->subject->name }}</span>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 group-hover:text-primary transition-colors leading-tight">
                                {{ $exam->name }}
                            </h3>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 mt-2.5 text-[11px] sm:text-sm text-gray-500 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $exam->date->translatedFormat('d M Y') }}
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ \Carbon\Carbon::parse($exam->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($exam->end_time)->format('H:i') }}
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="truncate max-w-[120px] sm:max-w-none">{{ $exam->teacher->user->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Action -->
                    <div class="flex items-center gap-3">
                        @if($status === 'submitted')
                            <a href="{{ route('student.results.detail', $exam->attempts->where('student_id', auth()->user()->student->id)->first()->id ?? 0) }}" class="w-full sm:w-auto px-5 py-2.5 text-center text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm active:scale-[0.98]">
                                Lihat Hasil
                            </a>
                        @elseif($status === 'active' || $status === 'in_progress')
                            <a href="{{ route('student.exam.show', $exam->id) }}" class="w-full sm:w-auto px-5 py-2.5 text-center text-xs sm:text-sm font-medium text-white bg-primary rounded-xl hover:bg-primary-600 transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2 active:scale-[0.98]">
                                <span>{{ $status === 'in_progress' ? 'Lanjutkan' : 'Mulai Ujian' }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        @else
                            <button disabled class="w-full sm:w-auto px-5 py-2.5 text-xs sm:text-sm font-medium text-gray-400 bg-gray-50 border border-gray-100 rounded-xl cursor-not-allowed">
                                Belum Dibuka
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <x-empty-state 
                title="Tidak Ada Ujian" 
                message="Tidak ada ujian yang ditemukan untuk filter ini. Anda bisa bersantai sejenak! ☕" 
                icon="coffee" 
            />
        @endforelse
    </div>

    @if(method_exists($exams, 'hasPages') && $exams->hasPages())
    <div class="mt-6">
        {{ $exams->links() }}
    </div>
    @endif
</div>
