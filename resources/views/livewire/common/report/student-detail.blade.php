@section('title', 'Detail Hasil Siswa')

<div class="space-y-6">
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6 sm:gap-4">
        <div class="flex items-center gap-3 sm:gap-4">
            <a href="{{ route($backRoute, $backParam) }}" class="p-2.5 rounded-xl hover:bg-gray-100 text-text-muted transition-colors border border-border-subtle group">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div class="min-w-0">
                <h2 class="font-black text-xl sm:text-2xl text-text-main leading-tight truncate uppercase tracking-tight italic">Hasil Ujian Siswa</h2>
                <div class="flex items-center gap-2 mt-0.5">
                    <p class="text-[10px] sm:text-sm font-bold text-primary truncate max-w-[150px] sm:max-w-none">{{ $student->name }}</p>
                    <span class="text-gray-300">•</span>
                    <p class="text-[10px] sm:text-sm font-black text-text-muted truncate max-w-[150px] sm:max-w-none uppercase tracking-widest italic">{{ $exam->name }}</p>
                </div>
            </div>
        </div>

        @if($attempt)
        @php
            $essayQuestionIds = $exam->questions->where('type', 'essay')->pluck('id');
            $gradedEssayCount = $attempt->answers
                ->whereIn('question_id', $essayQuestionIds)
                ->whereNotNull('is_correct')
                ->count();
            $hasPendingEssay = $essayQuestionIds->isNotEmpty() && $gradedEssayCount < $essayQuestionIds->count();
            $statusText = $hasPendingEssay ? 'PENDING PENILAIAN' : ($attempt->passed ? 'LULUS' : 'GAGAL');
            $statusColorClass = $hasPendingEssay ? 'text-amber-600' : ($attempt->passed ? 'text-green-600' : 'text-red-500');
        @endphp
        <div class="flex items-center gap-2 sm:gap-3">
             <div class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-white rounded-xl border border-gray-100 shadow-sm text-center">
                <div class="text-[8px] sm:text-[10px] text-text-muted uppercase tracking-widest font-black leading-tight">Total Nilai</div>
                <div class="text-lg sm:text-xl font-black {{ $statusColorClass }} mt-0.5 sm:mt-1">{{ $attempt->total_score }}</div>
            </div>
             <div class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-white rounded-xl border border-gray-100 shadow-sm text-center">
                <div class="text-[8px] sm:text-[10px] text-text-muted uppercase tracking-widest font-black leading-tight">Status</div>
                <div class="text-[10px] sm:text-sm font-black {{ $statusColorClass }} mt-1 sm:mt-1.5 px-2 py-0.5 bg-gray-50 rounded-lg">
                    {{ $statusText }}
                </div>
            </div>
        </div>
        @endif
    </div>

    @if($attempt)
    <!-- Answers List -->
    <div class="max-w-4xl mx-auto space-y-6">
        @foreach($exam->questions as $index => $question)
        @php
            $answer = $attempt->answers->where('question_id', $question->id)->first();
            $isAnswered = $answer !== null;
            $isCorrect = $answer?->is_correct ?? false;
            // If shuffle is on, we might need original order, but for report default order is fine or use pivot order
        @endphp
        <div class="bg-white rounded-[1.5rem] sm:rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 relative overflow-hidden group hover:border-primary/20 transition-all duration-300">
            <!-- Question Number -->
            <div class="absolute top-0 right-0 px-3 sm:px-4 py-1.5 sm:py-2 bg-gray-50 rounded-bl-xl sm:rounded-bl-2xl text-[10px] sm:text-xs font-black text-gray-400 border-l border-b border-gray-100 group-hover:text-primary transition-colors">
                #{{ $index + 1 }}
            </div>

            <div class="space-y-4">
                <!-- Question Text -->
                <div class="pr-12">
                     <div class="prose prose-sm max-w-none text-text-main">
                        {!! \App\Support\HtmlSanitizer::clean($question->text) !!}
                    </div>
                </div>

                <!-- Answer Details -->
                <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100 space-y-3">
                    @if($question->type === 'multiple_choice')
                        <div class="flex items-start gap-3">
                            <div class="w-24 shrink-0 text-xs font-bold text-gray-500 uppercase tracking-wide pt-1">Jawaban Siswa</div>
                            <div class="flex-1">
                                @php
                                    $selectedOption = $isAnswered ? $question->options->where('id', $answer->selected_option_id)->first() : null;
                                @endphp
                                <div class="font-medium {{ $isCorrect ? 'text-green-700' : 'text-red-700' }} flex items-center gap-2">
                                    @if($selectedOption)
                                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold border {{ $isCorrect ? 'bg-green-100 border-green-200 text-green-700' : 'bg-red-100 border-red-200 text-red-700' }}">
                                            {{ $selectedOption->label }}
                                        </span>
                                        <span>{{ strip_tags($selectedOption->text) }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Tidak menjawab</span>
                                    @endif

                                    @if($isCorrect)
                                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Show Correct Answer if Wrong -->
                        @if(!$isCorrect)
                        <div class="flex items-start gap-3 pt-3 border-t border-gray-200/50">
                            <div class="w-24 shrink-0 text-xs font-bold text-gray-500 uppercase tracking-wide pt-1">Kunci Jawaban</div>
                            <div class="flex-1">
                                @php
                                    $correctOption = $question->options->where('is_correct', true)->first();
                                @endphp
                                <div class="font-medium text-green-700 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold bg-green-100 border border-green-200 text-green-700">
                                        {{ $correctOption->label ?? '?' }}
                                    </span>
                                    <span>{{ strip_tags($correctOption->text ?? 'Kunci tidak ditemukan') }}</span>
                                </div>
                            </div>
                        </div>
                        @endif

                    @else
                        <!-- Essay -->
                        <div class="space-y-3">
                            <div>
                                <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Jawaban Siswa</div>
                                <div class="p-3 bg-white rounded-lg border border-gray-200 text-sm text-gray-800">
                                    {{ $answer->answer ?? '-' }}
                                </div>
                            </div>
                            
                            @if($answer?->teacher_notes)
                            <div class="bg-blue-50/50 rounded-lg p-3 border border-blue-100">
                                <div class="text-xs font-bold text-blue-600 uppercase tracking-wide mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                    Catatan Guru
                                </div>
                                <div class="text-sm text-blue-800">
                                    {{ $answer->teacher_notes }}
                                </div>
                            </div>
                            @endif

                            <div class="flex items-center justify-end gap-2 text-sm pt-2 border-t border-gray-200/50">
                                <span class="text-gray-500">Nilai:</span>
                                <span class="font-bold text-gray-900">{{ $answer->score_awarded ?? 0 }} / {{ $question->score }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <!-- Empty State -->
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <img src="{{ asset('img/not-found.png') }}" alt="Belum Mengerjakan" class="w-98 max-w-md mb-8">
        <h3 class="text-xl font-bold text-slate-800 mb-2">Siswa Belum Mengerjakan</h3>
        <p class="text-slate-500 max-w-md mx-auto">Siswa ini belum memulai atau menyelesaikan ujian, sehingga tidak ada data jawaban yang dapat ditampilkan.</p>
    </div>
    @endif
</div>
