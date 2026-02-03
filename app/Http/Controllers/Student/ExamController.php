<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExamController extends Controller
{
    public function show($id)
    {
        $student = Auth::user()->student;
        
        $exam = Exam::with(['questions.options'])->findOrFail($id);
        
        // 1. Check Attempt exists
        $attempt = ExamAttempt::where('exam_id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (!$attempt) {
            return redirect()->route('student.exam.start', $id);
        }

        if ($attempt->submitted_at) {
            return redirect()->route('student.results.detail', $attempt->id);
        }
        
        // Transform questions for Front-end
        $questions = $exam->questions->map(function($q, $index) {
             return [
                 'id' => $q->id,
                 'type' => $q->type === 'essay' ? 'essay' : 'multiple_choice', 
                 'text' => $q->text, // Changed from question_text which was wrong
                 'options' => $q->options->map(function($opt) {
                     return [
                         'id' => $opt->id,
                         'text' => $opt->text // Checked QuestionOption model, it is text
                     ];
                 })->toArray()
             ];
        })->values()->toArray();

        // Load existing answers
        // Map: question_id => selected_option_id (for MC) or answer (for Essay)
        $existingAnswers = StudentAnswer::where('exam_attempt_id', $attempt->id)
            ->get()
            ->mapWithKeys(function($ans) {
                // For MC, we want the option ID. For Essay, the text.
                // Assuming 'answer' currently holds the text/value?
                // StudentAnswer model now has 'selected_option_id'
                $val = $ans->selected_option_id ?? $ans->answer;
                return [$ans->question_id => $val];
            })
            ->toArray();

        // Calculate remaining time (same logic as Livewire TakeExam)
        $endTime = Carbon::parse($attempt->started_at)->addMinutes($exam->duration_minutes);
        $examEndTime = Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->end_time);
        $finalDeadline = $endTime->min($examEndTime);
        $remainingSeconds = max(0, now()->diffInSeconds($finalDeadline, false));
        
        // Auto-submit if time already expired
        if ($remainingSeconds <= 0) {
            return redirect()->route('student.results.detail', $attempt->id);
        }

        return view('student.exam.show', [
            'exam' => $exam,
            'attempt' => $attempt,
            'questions' => $questions,
            'existingAnswers' => $existingAnswers,
            'studentName' => $student->user->name,
            'studentNis' => $student->nis,
            'remainingSeconds' => $remainingSeconds // Pass calculated remaining time
        ]);
    }

    public function submit(Request $request, $id)
    {
        $student = Auth::user()->student;
        $exam = Exam::with(['questions'])->findOrFail($id);
        
        $attempt = ExamAttempt::where('exam_id', $id)
            ->where('student_id', $student->id)
            ->whereNull('submitted_at')
            ->firstOrFail();

        $answers = $request->input('answers', []);
        $totalScore = 0;
        
        // Map questions to easily access pivot score and options
        // We need options to check correctness
        $examQuestions = $exam->questions->keyBy('id');
        
        foreach ($answers as $questionId => $answerValue) {
            $question = $examQuestions[$questionId] ?? null;
            if (!$question) continue;

            $isOptionId = is_numeric($answerValue);
            $selectedOptionId = $isOptionId ? $answerValue : null;
            
            $isCorrect = false;
            $scoreAwarded = 0;
            
            // Check correctness for Multiple Choice
            if ($question->type !== 'essay' && $selectedOptionId) {
                $option = \App\Models\QuestionOption::find($selectedOptionId);
                if ($option && $option->question_id == $question->id && $option->is_correct) {
                    $isCorrect = true;
                    // Get score from Pivot (exam_questions)
                    $scoreAwarded = $question->pivot->score ?? 0;
                }
            }
            
            StudentAnswer::updateOrCreate(
                [
                    'exam_attempt_id' => $attempt->id,
                    'question_id' => $questionId
                ],
                [
                    'answer' => (string)$answerValue,
                    'selected_option_id' => $selectedOptionId,
                    'is_correct' => $isCorrect,
                    'score_awarded' => $scoreAwarded
                ]
            );
            
            $totalScore += $scoreAwarded;
        }

        // Calculate Final Results
        $maxScore = $exam->questions->sum('pivot.score');
        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;
        $passed = $percentage >= $exam->passing_grade;

        $attempt->update([
            'submitted_at' => now(),
            'status' => 'submitted', // or 'graded'
            'total_score' => $totalScore,
            'percentage' => $percentage,
            'passed' => $passed
        ]);

        return response()->json([
            'success' => true, 
            'redirect' => route('student.results.detail', $attempt->id)
        ]);
    }
    
    public function result_detail($id)
    {
        $student = Auth::user()->student;
        $attempt = ExamAttempt::with(['exam', 'answers.question'])->where('id', $id)->where('student_id', $student->id)->firstOrFail();
        
        return view('student.exam.result_detail', [
            'attempt' => $attempt,
            'exam' => $attempt->exam
        ]);
    }
}
