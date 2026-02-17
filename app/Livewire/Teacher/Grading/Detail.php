<?php

namespace App\Livewire\Teacher\Grading;

use App\Enums\ExamAttemptStatus;
use Livewire\Component;
use App\Services\ScoringService;
use Illuminate\Support\Facades\Gate;

class Detail extends Component
{
    public $attempt;
    public $student;
    public $exam;
    public $classroomFilter = '';
    public $returnPage = 1;
    
    // PG Data
    public $pgScore = 0;
    public $maxPgScore = 0;
    public $pgAnswers = [];
    
    // Essay Data (bound to UI)
    public $essayGrades = []; // [answer_id => ['score' => int, 'feedback' => string]]

    private function pageSessionKey(?int $examId = null): string
    {
        return 'grading_page_exam_' . ($examId ?? $this->exam->id);
    }

    public function mount($exam, $student)
    {
        $this->classroomFilter = (string) request()->query('classroomFilter', '');
        $sessionPage = (int) session($this->pageSessionKey((int) $exam), 1);
        $this->returnPage = max(1, (int) request()->query('gradingPage', $sessionPage));

        // 1. Fetch Attempt
        $this->attempt = \App\Models\ExamAttempt::where('exam_id', $exam)
            ->where('student_id', $student)
            ->with(['exam.questions', 'answers.question', 'student.user'])
            ->firstOrFail();

        Gate::authorize('grade', $this->attempt->exam);

        $this->exam = $this->attempt->exam;
        $this->student = $this->attempt->student;

        // Grading detail page is only relevant for exams with essay questions.
        if (!$this->exam->questions()->where('type', 'essay')->exists()) {
            $route = \Illuminate\Support\Facades\Auth::user()->isAdmin()
                ? 'admin.grading.index'
                : 'teacher.grading.index';

            return redirect()->route($route);
        }
        
        // 2. Process Data
        $this->prepareData();
    }
    
    public function prepareData()
    {
        $this->pgAnswers = [];
        $this->essayGrades = [];
        $this->pgScore = 0;
        $this->maxPgScore = 0;
        
        // Map questions by ID for easy access
        $questions = $this->exam->questions->keyBy('id');
        $answersByQuestion = $this->attempt->answers->keyBy('question_id');
        
        foreach ($this->attempt->answers as $answer) {
            $question = $answer->question; // or look up from $questions if eager loaded correctly
            
            if (!$question) continue;
            
            if ($question->type === 'multiple_choice') {
                // Determine correctness from pivot if needed, or answer's own calculation
                // StudentAnswer has 'score_awarded' and 'is_correct'
                
                $this->pgScore += (int) ($answer->score_awarded ?? 0);
                $this->maxPgScore += (int) ($question->pivot->score ?? $question->score ?? 0);
                
                $this->pgAnswers[] = [
                    'no' => count($this->pgAnswers) + 1, // Logic for numbering might need improvement if questions are shuffled
                    'question' => $question->text,
                    'student_answer' => $this->getOptionLabel($answer->selected_option_id),
                    'key' => 'Kunci: ' . $question->correct_option_label, // Need to fetch correct option logic?
                    // Simplified: Just use answer->is_correct
                    'is_correct' => $answer->is_correct
                ];
            }
        }

        // Always show all essay questions, including unanswered ones.
        $essayQuestions = $this->exam->questions->where('type', 'essay');
        foreach ($essayQuestions as $question) {
            $answer = $answersByQuestion->get($question->id);
            $rawAnswer = trim((string) ($answer?->answer ?? ''));
            $isEmptyAnswer = $rawAnswer === '';
            $maxScore = (int) ($question->pivot->score ?? $question->score ?? 0);

            $this->essayGrades[$question->id] = [
                'answer_id' => $answer?->id,
                'question_id' => (int) $question->id,
                'question' => $question->text,
                'student_answer' => $isEmptyAnswer ? null : $answer?->answer,
                'is_empty' => $isEmptyAnswer,
                'key' => $question->explanation ?? 'Lihat kunci jawaban di bank soal',
                'max_score' => $maxScore,
                'score' => (int) ($answer?->score_awarded ?? 0),
                'feedback' => $answer?->teacher_feedback ?? '',
                'is_correct' => $answer?->is_correct,
            ];
        }
        
        // Fallback for MaxPG if it's 0 (maybe no PG questions)
        if ($this->maxPgScore == 0) {
            // Recalculate from pure questions if answers missing
            $this->maxPgScore = $questions->where('type', 'multiple_choice')->sum('score');
        }
    }
    
    private function getOptionLabel($optionId)
    {
        if (!$optionId) return '-';
        $opt = \App\Models\QuestionOption::find($optionId);
        return $opt ? $opt->label . '. ' . $opt->text : '-';
    }

    public function updatedEssayGrades($value, $key)
    {
        // Validation for Max Score
        // Key format: {answer_id}.score
        $parts = explode('.', $key);
        if (count($parts) == 2 && $parts[1] == 'score') {
            $answerId = $parts[0];
            $max = $this->essayGrades[$answerId]['max_score'];
            
            if ((int)$value > $max) {
                $this->essayGrades[$answerId]['score'] = $max;
            }
            if ((int)$value < 0) {
                $this->essayGrades[$answerId]['score'] = 0;
            }
        }
    }

    public function getCurrentTotalScoreProperty()
    {
        $essayTotal = collect($this->essayGrades)->sum('score');
        return $this->pgScore + $essayTotal;
    }

    public function finishGrading()
    {
        Gate::authorize('grade', $this->exam);

        $scoringService = app(ScoringService::class);

        \Illuminate\Support\Facades\DB::transaction(function () use ($scoringService) {
            foreach ($this->essayGrades as $questionId => $data) {
                $max = (int) ($data['max_score'] ?? 0);
                $score = (int) ($data['score'] ?? 0);
                $score = max(0, min($score, $max));
                $isEmptyAnswer = (bool) ($data['is_empty'] ?? false);

                // Teacher assessment for essay:
                // score > 0 => correct, score = 0 => incorrect.
                $isCorrect = $score > 0;

                \App\Models\StudentAnswer::updateOrCreate(
                    [
                        'exam_attempt_id' => $this->attempt->id,
                        'question_id' => (int) $questionId,
                    ],
                    [
                        'answer' => $isEmptyAnswer ? null : (string) ($data['student_answer'] ?? ''),
                        'selected_option_id' => null,
                        'score_awarded' => $score,
                        'is_correct' => $isCorrect,
                        'teacher_feedback' => (string) ($data['feedback'] ?? ''),
                    ]
                );
            }

            $summary = $scoringService->recalculateAttempt($this->exam, $this->attempt);

            $this->attempt->update([
                'total_score' => $summary['total_score'],
                'percentage' => $summary['percentage'],
                'status' => ExamAttemptStatus::Graded, // Final status
                'passed' => $summary['passed'],
            ]);
        });

        $route = \Illuminate\Support\Facades\Auth::user()->isAdmin()
            ? 'admin.grading.show'
            : 'teacher.grading.show';

        session([$this->pageSessionKey() => $this->returnPage]);

        $params = ['exam' => $this->exam->id];
        if (filled($this->classroomFilter)) {
            $params['classroomFilter'] = $this->classroomFilter;
        }
        if ($this->returnPage > 1) {
            $params['gradingPage'] = $this->returnPage;
        }

        return redirect()->route($route, $params)
            ->with('success', 'Penilaian tersimpan. Lanjutkan menilai siswa lainnya.');
    }

    public function publishScore()
    {
        Gate::authorize('grade', $this->exam);

        $scoringService = app(ScoringService::class);

        \Illuminate\Support\Facades\DB::transaction(function () use ($scoringService) {
            foreach ($this->essayGrades as $questionId => $data) {
                $max = (int) ($data['max_score'] ?? 0);
                $score = (int) ($data['score'] ?? 0);
                $score = max(0, min($score, $max));
                $isEmptyAnswer = (bool) ($data['is_empty'] ?? false);
                $isCorrect = $score > 0;

                \App\Models\StudentAnswer::updateOrCreate(
                    [
                        'exam_attempt_id' => $this->attempt->id,
                        'question_id' => (int) $questionId,
                    ],
                    [
                        'answer' => $isEmptyAnswer ? null : (string) ($data['student_answer'] ?? ''),
                        'selected_option_id' => null,
                        'score_awarded' => $score,
                        'is_correct' => $isCorrect,
                        'teacher_feedback' => (string) ($data['feedback'] ?? ''),
                    ]
                );
            }

            $summary = $scoringService->recalculateAttempt($this->exam, $this->attempt);

            $this->attempt->update([
                'total_score' => $summary['total_score'],
                'percentage' => $summary['percentage'],
                'status' => ExamAttemptStatus::Graded,
                'passed' => $summary['passed'],
            ]);

            $this->exam->update(['is_published' => true]);
        });

        $route = \Illuminate\Support\Facades\Auth::user()->isAdmin()
            ? 'admin.grading.show'
            : 'teacher.grading.show';

        session([$this->pageSessionKey() => $this->returnPage]);

        $params = ['exam' => $this->exam->id];
        if (filled($this->classroomFilter)) {
            $params['classroomFilter'] = $this->classroomFilter;
        }
        if ($this->returnPage > 1) {
            $params['gradingPage'] = $this->returnPage;
        }

        return redirect()->route($route, $params)
            ->with('success', 'Nilai berhasil diterbitkan.');
    }

    public function render()
    {
        $isAdmin = \Illuminate\Support\Facades\Auth::user()->isAdmin();
        $backParams = ['exam' => $this->exam->id];
        if (filled($this->classroomFilter)) {
            $backParams['classroomFilter'] = $this->classroomFilter;
        }
        if ($this->returnPage > 1) {
            $backParams['gradingPage'] = $this->returnPage;
        }

        return view('teacher.grading.detail', [
            'student_name' => $this->student->user->name,
            'grade' => $this->exam->class,
            'backRoute' => $isAdmin ? 'admin.grading.show' : 'teacher.grading.show',
            'backParams' => $backParams,
        ])->layout($isAdmin ? 'layouts.admin' : 'layouts.teacher')->title('Koreksi - ' . $this->student->user->name);
    }
}
