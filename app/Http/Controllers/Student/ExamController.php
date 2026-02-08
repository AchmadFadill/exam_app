<?php

namespace App\Http\Controllers\Student;

use App\Enums\ExamAttemptStatus;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\StudentAnswer;
use App\Services\ScoringService;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        
        // Get student for seeded shuffling
        $student = Auth::user()->student;
        
        // Transform questions for Front-end
        $questionsCollection = $exam->questions;
        
        // Shuffle questions if enabled (seeded for consistency per student)
        if ($exam->shuffle_questions) {
            $seed = $student->id + $exam->id;
            $questionsCollection = $questionsCollection->shuffle($seed);
        }
        
        $questions = $questionsCollection->map(function($q, $index) use ($exam, $student) {
            $options = $q->options;
            
            // Shuffle answer options if enabled (only for multiple choice)
            if ($exam->shuffle_answers && $q->type === 'multiple_choice') {
                $seed = $student->id + $exam->id + $q->id;
                $options = $options->shuffle($seed);
            }
            
            return [
                'id' => $q->id,
                'type' => $q->type === 'essay' ? 'essay' : 'multiple_choice', 
                'text' => HtmlSanitizer::clean($q->text),
                'image_path' => $q->image_path ? \Illuminate\Support\Facades\Storage::url($q->image_path) : null,
                'options' => $options->map(function($opt) {
                    return [
                        'id' => $opt->id,
                        'text' => strip_tags(HtmlSanitizer::clean($opt->text))
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
        $remainingSeconds = (int) max(0, now()->diffInSeconds($finalDeadline, false));
        
        // Auto-submit if time already expired
        if ($remainingSeconds <= 0) {
            return redirect()->route('student.results.detail', $attempt->id);
        }

        return view('student.exam.show', [
            'exam' => $exam, // Pass full exam object for security settings
            'attempt' => $attempt,
            'questions' => $questions,
            'existingAnswers' => $existingAnswers,
            'studentName' => $student->user->name,
            'studentNis' => $student->nis,
            'remainingSeconds' => $remainingSeconds
        ]);
    }

    public function submit(Request $request, $id, ScoringService $scoringService)
    {
        $student = Auth::user()->student;
        $exam = Exam::with(['questions', 'teacher.user'])->findOrFail($id);
        
        $attempt = ExamAttempt::where('exam_id', $id)
            ->where('student_id', $student->id)
            ->where('status', ExamAttemptStatus::InProgress->value)
            ->whereNull('submitted_at')
            ->firstOrFail();

        $answers = $request->input('answers', []);
        
        DB::transaction(function () use ($answers, $exam, $attempt, $scoringService): void {
            $examQuestions = $exam->questions->keyBy('id');

            foreach ($answers as $questionId => $answerValue) {
                $question = $examQuestions[(int) $questionId] ?? null;
                if (!$question) {
                    continue;
                }

                $scored = $scoringService->scoreSingleAnswer($exam, $question, $answerValue);

                StudentAnswer::updateOrCreate(
                    [
                        'exam_attempt_id' => $attempt->id,
                        'question_id' => (int) $questionId,
                    ],
                    $scored
                );
            }

            $attemptSummary = $scoringService->recalculateAttempt($exam, $attempt);

            $attempt->update([
                'submitted_at' => now(),
                'status' => $attemptSummary['has_essay'] ? ExamAttemptStatus::Submitted : ExamAttemptStatus::Graded,
                'total_score' => $attemptSummary['total_score'],
                'percentage' => $attemptSummary['percentage'],
                'passed' => $attemptSummary['passed'],
            ]);
        });

        // Send Notification to Teacher
        try {
            $teacher = $exam->teacher->user;
            $teacher->notify(new \App\Notifications\ExamSubmitted($exam, $student->user));
        } catch (\Exception $e) {
            // Log error or ignore if notification fails to prevent exam error
        }

        return response()->json([
            'success' => true, 
            'redirect' => route('student.results.detail', $attempt->id)
        ]);
    }

    public function saveAnswer(Request $request, $id, ScoringService $scoringService)
    {
        $student = Auth::user()->student;
        $attempt = ExamAttempt::where('exam_id', $id)
            ->where('student_id', $student->id)
            ->where('status', ExamAttemptStatus::InProgress->value)
            ->whereNull('submitted_at')
            ->firstOrFail();
            
        // Basic validation
        $questionId = $request->input('question_id');
        $answerValue = $request->input('answer');
        
        if (!$questionId) {
            return response()->json(['success' => false, 'message' => 'Missing question ID']);
        }

        // Check if time is up (optional but good for security)
        $exam = $attempt->exam;
        $endTime = Carbon::parse($attempt->started_at)->addMinutes($exam->duration_minutes);
        // Add a small buffer (e.g. 1-2 mins) for latency
        if (now()->diffInSeconds($endTime, false) < -60) {
             return response()->json(['success' => false, 'message' => 'Time is up'], 403);
        }

        $question = Question::where('id', $questionId)
            ->whereHas('exams', function ($query) use ($attempt) {
                $query->where('exams.id', $attempt->exam_id);
            })
            ->first();

        if (!$question) {
            return response()->json(['success' => false, 'message' => 'Invalid question for this exam'], 403);
        }

        $scored = $scoringService->scoreSingleAnswer($exam, $question, $answerValue);

        StudentAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $questionId
            ],
            $scored
        );
        
        // touch the attempt to update 'updated_at' for live monitoring sorting
        $attempt->touch();

        return response()->json(['success' => true]);
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

    public function statusCheck($id)
    {
        $student = Auth::user()->student;
        $attempt = ExamAttempt::where('exam_id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (!$attempt) {
            return response()->json(['force_stop' => false]);
        }

        $attemptStatus = $attempt->status instanceof ExamAttemptStatus
            ? $attempt->status
            : ExamAttemptStatus::tryFrom((string) $attempt->status);

        if ($attempt->submitted_at || ($attemptStatus?->isFinalized() ?? false)) {
            return response()->json([
                'force_stop' => true,
                'redirect' => route('student.results.detail', $attempt->id) // Or index
            ]);
        }

        return response()->json(['force_stop' => false]);
    }

    public function logViolation(Request $request, $id)
    {
        try {
            $student = Auth::user()->student;
            // 1. Validate Attempt Exists and is In Progress
            $attempt = ExamAttempt::where('exam_id', $id)
                ->where('student_id', $student->id)
                ->where('status', ExamAttemptStatus::InProgress->value)
                ->whereNull('submitted_at')
                ->first();

            if (!$attempt) {
                return response()->json(['success' => false, 'message' => 'No active attempt'], 404);
            }

            $type = $request->input('type', 'tab_switch');
            $message = $request->input('message', 'Violation detected');

            // 2. Create Activity Log
            \App\Models\ExamActivity::create([
                'user_id' => Auth::id(),
                'exam_id' => $id,
                'exam_attempt_id' => $attempt->id,
                'type' => $type,
                'severity' => in_array($type, ['tab_switch', 'fullscreen_exit']) ? 'warning' : 'info',
                'message' => $message,
                'metadata' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            ]);

            // 3. Increment Counter
            if ($type === 'tab_switch' || $type === 'fullscreen_exit') {
                $attempt->increment('tab_switches');
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("API Log Violation Failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
