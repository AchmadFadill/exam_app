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
    
    // PG Data
    public $pgScore = 0;
    public $maxPgScore = 0;
    public $pgAnswers = [];
    
    // Essay Data (bound to UI)
    public $essayGrades = []; // [answer_id => ['score' => int, 'feedback' => string]]

    public function mount($exam, $student)
    {
        // 1. Fetch Attempt
        $this->attempt = \App\Models\ExamAttempt::where('exam_id', $exam)
            ->where('student_id', $student)
            ->with(['exam.questions', 'answers.question', 'student.user'])
            ->firstOrFail();

        Gate::authorize('grade', $this->attempt->exam);

        $this->exam = $this->attempt->exam;
        $this->student = $this->attempt->student;
        
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
        
        foreach ($this->attempt->answers as $answer) {
            $question = $answer->question; // or look up from $questions if eager loaded correctly
            
            if (!$question) continue;
            
            if ($question->type === 'multiple_choice') {
                // Determine correctness from pivot if needed, or answer's own calculation
                // StudentAnswer has 'score_awarded' and 'is_correct'
                
                $this->pgScore += $answer->score_awarded;
                $this->maxPgScore += $question->score; // Or pivot score if available? Assuming question score for now.
                
                $this->pgAnswers[] = [
                    'no' => count($this->pgAnswers) + 1, // Logic for numbering might need improvement if questions are shuffled
                    'question' => $question->text,
                    'student_answer' => $this->getOptionLabel($answer->selected_option_id),
                    'key' => 'Kunci: ' . $question->correct_option_label, // Need to fetch correct option logic?
                    // Simplified: Just use answer->is_correct
                    'is_correct' => $answer->is_correct
                ];
            } else {
                // Essay
                // Initialize grades
                $this->essayGrades[$answer->id] = [
                    'question' => $question->text,
                    'student_answer' => $answer->answer,
                    'key' => $question->explanation ?? 'Lihat kunci jawaban di bank soal',
                    'max_score' => $question->score,
                    'score' => $answer->score_awarded ?? 0, // Default to 0 or existing score
                    'feedback' => $answer->teacher_feedback ?? '' // Ensure column exists? Need to check migration usage or json
                ];
            }
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
            foreach ($this->essayGrades as $answerId => $data) {
                $score = (int)$data['score'];
                
                \App\Models\StudentAnswer::where('id', $answerId)->update([
                    'score_awarded' => $score,
                    // 'feedback' => $data['feedback'] // Need to verify if 'feedback' column exists on student_answers?
                ]);
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

        return redirect()->route($route, $this->exam->id)
            ->with('success', 'Penilaian berhasil disimpan!');
    }

    public function render()
    {
        return view('teacher.grading.detail', [
            'student_name' => $this->student->user->name,
            'grade' => $this->exam->class,
        ])->layout(\Illuminate\Support\Facades\Auth::user()->isAdmin() ? 'layouts.admin' : 'layouts.teacher')->title('Koreksi - ' . $this->student->user->name);
    }
}
