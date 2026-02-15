<x-student-layout>
    <x-slot name="title">Detail Hasil Ujian</x-slot>
@php
    $correctCount = $attempt->answers->filter(fn($a) => $a->is_correct === true)->count();
    $wrongCount = $attempt->answers->filter(fn($a) => $a->is_correct === false)->count();
    $pendingCount = $attempt->answers->filter(fn($a) => is_null($a->is_correct))->count();
    $essayQuestionIds = $exam->questions->where('type', 'essay')->pluck('id');
    $gradedEssayCount = $attempt->answers
        ->whereIn('question_id', $essayQuestionIds)
        ->whereNotNull('is_correct')
        ->count();
    $hasPendingEssay = $essayQuestionIds->isNotEmpty() && $gradedEssayCount < $essayQuestionIds->count();
@endphp
<div class="container mx-auto px-4 sm:px-6 py-4 sm:py-8">
    <!-- Back Button -->
    <div class="mb-5 sm:mb-6">
            <x-button variant="soft" href="{{ route('student.results') }}" class="px-5 sm:px-6 py-2.5 sm:py-3 rounded-xl text-[10px] sm:text-xs font-black bg-blue-100 hover:bg-blue-200 text-blue-600 hover:text-blue-700 border-none shadow-none">
                &larr; KEMBALI 
            </x-button>
    </div>

    <!-- Result Header Card -->
    <div class="bg-bg-surface dark:bg-bg-surface rounded-3xl shadow-sm border border-border-subtle dark:border-border-subtle overflow-hidden mb-6 sm:mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 sm:px-8 py-8 sm:py-10 text-white">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8 sm:gap-6">
                <div class="text-center md:text-left">
                    <h2 class="text-2xl sm:text-3xl font-bold mb-2">{{ $exam->name }}</h2>
                    <p class="text-blue-100 text-base sm:text-lg">{{ $exam->subject->name ?? 'Mata Pelajaran' }}</p>
                    <div class="flex items-center justify-center md:justify-start mt-4 text-blue-100 text-xs">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Dikerjakan pada {{ $attempt->started_at->translatedFormat('d F Y, H:i') }}
                    </div>
                </div>
                <div class="relative overflow-hidden bg-white/10 backdrop-blur-md rounded-2xl p-5 sm:p-6 text-center border border-white/20 group w-full sm:w-auto min-w-[160px]">
                    <!-- Content -->
                    <div class="relative z-10 text-center">
                        <div class="text-[10px] sm:text-sm font-medium uppercase tracking-wider mb-1 text-white opacity-80">Nilai Akhir</div>
                        @if($exam->show_score_to_student)
                            <div class="text-5xl sm:text-6xl font-black text-white drop-shadow-sm">{{ number_format($attempt->percentage ?? 0, 1) }}</div>
                            @if($hasPendingEssay)
                                <div class="mt-2 text-[10px] font-semibold px-3 py-1 bg-amber-500 text-white rounded-full inline-block shadow-lg">PENDING PENILAIAN</div>
                            @elseif($attempt->passed)
                                <div class="mt-2 text-[10px] font-semibold px-3 py-1 bg-green-500 text-white rounded-full inline-block shadow-lg">LULUS KKM</div>
                            @else
                                <div class="mt-2 text-[10px] font-semibold px-3 py-1 bg-red-500 text-white rounded-full inline-block shadow-lg">TIDAK LULUS</div>
                            @endif
                        @else
                            <div class="text-lg sm:text-xl font-black text-white drop-shadow-sm">Disembunyikan</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Summary -->
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 lg:divide-x divide-border-subtle dark:divide-border-subtle border-t border-border-subtle dark:border-border-subtle">
            <div class="p-4 sm:p-6 text-center border-r border-border-subtle lg:border-r-0">
                <div class="text-[10px] sm:text-sm font-medium text-text-muted mb-1 leading-tight">Total Soal</div>
                <div class="text-xl sm:text-2xl font-bold text-text-main">{{ $exam->questions->count() }}</div>
            </div>
            <div class="p-4 sm:p-6 text-center border-b sm:border-b-0">
                <div class="text-[10px] sm:text-sm font-medium text-green-600 mb-1 leading-tight">Benar</div>
                <div class="text-xl sm:text-2xl font-bold text-green-600">{{ $correctCount }}</div>
            </div>
            <div class="p-4 sm:p-6 text-center border-r border-border-subtle lg:border-r-0">
                <div class="text-[10px] sm:text-sm font-medium text-red-600 mb-1 leading-tight">Salah</div>
                <div class="text-xl sm:text-2xl font-bold text-red-600">{{ $wrongCount }}</div>
            </div>
            <div class="p-4 sm:p-6 text-center">
                <div class="text-[10px] sm:text-sm font-medium text-text-muted mb-1 leading-tight">Score Total</div>
                <div class="text-xl sm:text-2xl font-bold text-text-muted">
                    {{ $exam->show_score_to_student ? $attempt->total_score : '-' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Discussion Section -->
    <div class="mb-5 sm:mb-6 flex items-center justify-between">
        <h4 class="text-text-main text-lg sm:text-xl font-bold">Pembahasan Jawaban</h4>
    </div>

    @if(!$exam->show_answers_to_student)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 text-amber-800 px-5 py-4 text-sm font-semibold">
            Pembahasan jawaban disembunyikan oleh guru untuk ujian ini.
        </div>
    @else
    <div class="space-y-6">
        @foreach($exam->questions as $index => $question)
            @php
                $studentAnswer = $attempt->answers->where('question_id', $question->id)->first();
                $isEssay = $question->type === 'essay';
                $userOptionId = $studentAnswer->selected_option_id ?? null;
                $userOption = $studentAnswer?->selectedOption;
                $selectedOptionMatchesQuestion = $userOption && (int) $userOption->question_id === (int) $question->id;

                // Prefer stored flag; fallback to option correctness when relation is consistent.
                $isCorrect = $studentAnswer && (
                    $studentAnswer->is_correct === true
                    || (
                        !$isEssay
                        && $selectedOptionMatchesQuestion
                        && (bool) $userOption->is_correct
                    )
                );
                $correctOption = $question->options->where('is_correct', true)->first();
                $hasAnswer = $studentAnswer && (
                    $isEssay
                        ? filled(trim((string) ($studentAnswer->answer ?? '')))
                        : (!is_null($userOptionId) || filled(trim((string) ($studentAnswer->answer ?? ''))))
                );
                $isWrong = $hasAnswer && !$isCorrect && (!$isEssay || !is_null($studentAnswer?->is_correct));
                $maxQuestionScore = (float) ($question->pivot->score ?? $question->score ?? 0);
                $awardedScore = $studentAnswer ? (float) ($studentAnswer->score_awarded ?? 0) : 0.0;
            @endphp
        <div class="bg-bg-surface dark:bg-bg-surface rounded-2xl shadow-sm border border-border-subtle dark:border-border-subtle overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-bold text-text-muted">SOAL NO. {{ $index + 1 }}</span>
                    @if($isEssay && is_null($studentAnswer?->is_correct))
                    <span class="px-3 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9V9h2v4zm0-6H9V5h2v2z"></path></svg>
                        POIN: Pending / {{ number_format($maxQuestionScore, 1) }}
                    </span>
                    @elseif(!$hasAnswer)
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-full flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11H9v4h2V7zm0 6H9v2h2v-2z"></path></svg>
                        POIN: 0.0 / {{ number_format($maxQuestionScore, 1) }}
                    </span>
                    @else
                    <span class="px-3 py-1 {{ $awardedScore > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} text-xs font-bold rounded-full flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11H9v4h2V7zm0 6H9v2h2v-2z"></path></svg>
                        POIN: {{ number_format($awardedScore, 1) }} / {{ number_format($maxQuestionScore, 1) }}
                    </span>
                    @endif
                </div>
                <div class="prose max-w-none text-text-main mb-4">
                    {!! \App\Support\HtmlSanitizer::clean($question->text) !!}
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="text-xs sm:text-sm">
                        <div class="font-semibold text-text-muted mb-2">Jawaban Kamu:</div>
                        <div class="p-3 {{ $isCorrect ? 'bg-green-50 border-green-200 text-green-700' : ($isWrong ? 'bg-red-50 border-red-200 text-red-700' : 'bg-gray-50 border-gray-200 text-gray-700') }} border rounded-xl font-medium">
                             {{ $isEssay ? ($studentAnswer->answer ?? 'Belum ada jawaban essay') : (($selectedOptionMatchesQuestion ? $userOption->text : null) ?? (!is_null($userOptionId) ? 'Jawaban tersimpan' : 'Tidak Menjawab')) }}
                        </div>
                    </div>
                     <div class="text-xs sm:text-sm">
                        <div class="font-semibold text-text-muted mb-2">Kunci Jawaban:</div>
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-blue-700 font-medium">
                             {{ $isEssay ? 'Dinilai manual oleh guru' : ($correctOption->text ?? '-') }}
                        </div>
                    </div>
                </div>

                @if($isEssay)
                @php
                    $teacherFeedback = trim((string) ($studentAnswer?->teacher_feedback ?? ''));
                @endphp
                <div class="mt-4 text-xs sm:text-sm">
                    <div class="font-semibold text-text-muted mb-2">Feedback Guru:</div>
                    <div class="p-3 border rounded-xl font-medium {{ is_null($studentAnswer?->is_correct) ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-indigo-50 border-indigo-200 text-indigo-700' }}">
                        @if(is_null($studentAnswer?->is_correct))
                            Feedback akan muncul setelah jawaban essay selesai dinilai.
                        @else
                            {{ $teacherFeedback !== '' ? $teacherFeedback : 'Guru belum memberikan feedback untuk jawaban ini.' }}
                        @endif
                    </div>
                </div>
                @endif

                @if($question->explanation)
                <!-- Teacher Explanation -->
                <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <div class="flex items-center mb-2">
                         <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-2">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                         </div>
                         <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Catatan Dari Guru</span>
                    </div>
                    <p class="text-sm text-gray-600">
                        {{ $question->explanation }}
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
</x-student-layout>
