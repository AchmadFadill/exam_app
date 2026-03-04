<?php

namespace App\Http\Controllers\Student;

use App\Actions\Exam\ProcessExamSubmissionAction;
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
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExamController extends Controller
{
    public function show($id)
    {
        $student = Auth::user()->student;
        $exam = Exam::with('examQuestions')->findOrFail($id);
        
        // 1. Check Attempt exists
        $attempt = ExamAttempt::where('exam_id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (!$attempt) {
            return redirect()->route('student.exam.start', $id);
        }

        if ($attempt->submitted_at) {
            return redirect($this->resultRedirectUrl($attempt));
        }
        
        // Render order must come from persisted attempt snapshot to avoid randomization drift.
        $questionIds = $this->resolveQuestionOrder($exam, $attempt);
        $questionMap = Question::with('options')
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        $questions = collect($questionIds)
            ->map(fn ($qId) => $questionMap->get((int) $qId))
            ->filter()
            ->values()
            ->map(function ($q) use ($attempt, $exam) {
                $options = $this->resolveOptionOrder($q->options, $attempt, (int) $q->id, $exam);

            return [
                'id' => $q->id,
                'type' => $q->type === 'essay' ? 'essay' : 'multiple_choice', 
                'text' => HtmlSanitizer::clean($q->text),
                'image_path' => $q->image_path ? \Illuminate\Support\Facades\Storage::url($q->image_path) : null,
                'options' => $options->map(function($opt) {
                    $cleanOptionText = strip_tags(HtmlSanitizer::clean($opt->text));

                    return [
                        'id' => $opt->id,
                        // Decode HTML entities so KaTeX sees raw symbols (e.g. "&" in matrix).
                        'text' => html_entity_decode($cleanOptionText, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'image_path' => $opt->image_path ? \Illuminate\Support\Facades\Storage::url($opt->image_path) : null,
                    ];
                })->values()->toArray()
            ];
        })->toArray();

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

    /**
     * @return array<int>
     */
    private function resolveQuestionOrder(Exam $exam, ExamAttempt $attempt): array
    {
        // Fixed-order mode must always follow exam_questions.order.
        // Do not trust old attempt snapshots created by previous logic.
        if (!(bool) $exam->shuffle_questions) {
            return $exam->examQuestions
                ->sortBy(fn ($row) => sprintf('%010d-%010d', (int) $row->order, (int) $row->question_id))
                ->pluck('question_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        if (!empty($attempt->question_order) && is_array($attempt->question_order)) {
            return array_values(array_map('intval', $attempt->question_order));
        }

        return $exam->examQuestions
            ->sortBy(fn ($row) => sprintf('%010d-%010d', (int) $row->order, (int) $row->question_id))
            ->pluck('question_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, \App\Models\QuestionOption>  $options
     * @return Collection<int, \App\Models\QuestionOption>
     */
    private function resolveOptionOrder(Collection $options, ExamAttempt $attempt, int $questionId, Exam $exam): Collection
    {
        // In fixed answer-order mode, never apply snapshot reorder.
        if (!(bool) $exam->shuffle_answers) {
            return $options->values();
        }

        $optionIds = $attempt->options_order[(string) $questionId] ?? null;
        if (!is_array($optionIds) || empty($optionIds)) {
            return $options->values();
        }

        $optionMap = $options->keyBy('id');

        return collect($optionIds)
            ->map(fn ($optionId) => $optionMap->get((int) $optionId))
            ->filter()
            ->values();
    }

    public function submit(Request $request, $id, ProcessExamSubmissionAction $processExamSubmission)
    {
        $student = Auth::user()->student;
        $exam = Exam::with(['questions', 'teacher.user'])->findOrFail($id);
        
        $attempt = ExamAttempt::where('exam_id', $id)
            ->where('student_id', $student->id)
            ->where('status', ExamAttemptStatus::InProgress->value)
            ->whereNull('submitted_at')
            ->firstOrFail();

        $answers = $request->input('answers', []);

        $attempt = $processExamSubmission->execute($exam, $attempt, is_array($answers) ? $answers : []);

        // Send Notification to Teacher
        try {
            $teacher = $exam->teacher->user;
            $teacher->notify(new \App\Notifications\ExamSubmitted($exam, $student->user));
        } catch (\Exception $e) {
            // Log error or ignore if notification fails to prevent exam error
        }

        return response()->json([
            'success' => true, 
            'redirect' => $this->resultRedirectUrl($attempt)
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

        if ($question->type === 'multiple_choice') {
            if (!is_numeric($answerValue)) {
                return response()->json(['success' => false, 'message' => 'Invalid multiple-choice payload'], 422);
            }

            $answerValue = (int) $answerValue;

            $isOptionBelongsToQuestion = \App\Models\QuestionOption::query()
                ->where('id', $answerValue)
                ->where('question_id', $question->id)
                ->exists();

            if (!$isOptionBelongsToQuestion) {
                return response()->json(['success' => false, 'message' => 'Option does not belong to question'], 422);
            }
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
    
    public function result_detail($id, ScoringService $scoringService)
    {
        $student = Auth::user()->student;
        $attempt = ExamAttempt::with([
            'exam.questions.options',
            'answers.question',
            'answers.selectedOption',
        ])->where('id', $id)->where('student_id', $student->id)->firstOrFail();

        // Re-sync scoring to avoid stale correctness flags on result display.
        $summary = $scoringService->recalculateAttempt($attempt->exam, $attempt);
        $attempt->forceFill([
            'total_score' => $summary['total_score'],
            'percentage' => $summary['percentage'],
            'passed' => $summary['passed'],
        ])->save();
        $attempt->refresh();
        
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
            return response()->json(['force_stop' => false, 'blocked' => false]);
        }

        $attempt->loadMissing('exam:id,enable_tab_tolerance,tab_tolerance');
        $attemptStatus = $attempt->status instanceof ExamAttemptStatus
            ? $attempt->status
            : ExamAttemptStatus::tryFrom((string) $attempt->status);

        if ($attemptStatus === ExamAttemptStatus::Blocked) {
            return response()->json([
                'force_stop' => false,
                'blocked' => true,
                'message' => 'Ujian Anda diblokir sementara. Menunggu keputusan guru/pengawas.',
            ]);
        }

        if ($attempt->submitted_at || ($attemptStatus?->isFinalized() ?? false)) {
            return response()->json([
                'force_stop' => true,
                'blocked' => false,
                'redirect' => $this->resultRedirectUrl($attempt)
            ]);
        }

        return response()->json([
            'force_stop' => false,
            'blocked' => false,
        ]);
    }

    public function heartbeat(Request $request, $id)
    {
        $student = Auth::user()->student;

        $attempt = ExamAttempt::where('exam_id', $id)
            ->where('student_id', $student->id)
            ->where('status', ExamAttemptStatus::InProgress->value)
            ->whereNull('submitted_at')
            ->first();

        if (!$attempt) {
            return response()->json(['success' => false, 'message' => 'No active attempt'], 404);
        }

        $attempt->forceFill(['last_seen_at' => now()])->save();

        return response()->json([
            'success' => true,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function logViolation(Request $request, $id)
    {
        try {
            $student = Auth::user()->student;
            // 1. Validate Attempt Exists and is In Progress
            $attempt = ExamAttempt::where('exam_id', $id)
                ->where('student_id', $student->id)
                ->whereIn('status', [ExamAttemptStatus::InProgress->value, ExamAttemptStatus::Blocked->value])
                ->whereNull('submitted_at')
                ->first();

            if (!$attempt) {
                return response()->json(['success' => false, 'message' => 'No active attempt'], 404);
            }

            $type = $request->input('type', 'tab_switch');
            $message = $request->input('message', 'Violation detected');
            $count = (int) $request->input('count', 1);
            $count = max(1, min($count, 10));

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
                    'user_agent' => $request->userAgent(),
                    'count' => $count,
                ]
            ]);

            // 3. Increment Counter
            if ($type === 'tab_switch' || $type === 'fullscreen_exit') {
                $attempt->increment('tab_switches', $count);
            }

            $attempt->refresh()->loadMissing('exam:id,enable_tab_tolerance,tab_tolerance');

            $max = (int) ($attempt->exam?->tab_tolerance ?? 0);
            $enabled = (bool) ($attempt->exam?->enable_tab_tolerance ?? false);
            $isBlocked = false;

            if (
                $enabled
                && $max > 0
                && (int) $attempt->tab_switches >= $max
                && !$attempt->submitted_at
                && ($attempt->status instanceof ExamAttemptStatus ? $attempt->status : ExamAttemptStatus::tryFrom((string) $attempt->status)) !== ExamAttemptStatus::Blocked
            ) {
                $attempt->update([
                    'status' => ExamAttemptStatus::Blocked,
                ]);
                $attempt->refresh();
                $isBlocked = true;

                \App\Models\ExamActivity::create([
                    'user_id' => Auth::id(),
                    'exam_id' => $id,
                    'exam_attempt_id' => $attempt->id,
                    'type' => 'blocked',
                    'severity' => 'critical',
                    'message' => 'Ujian diblokir otomatis karena pelanggaran melebihi batas. Menunggu keputusan guru.',
                    'metadata' => [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'tab_switches' => (int) $attempt->tab_switches,
                        'max' => $max,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'tab_switches' => (int) $attempt->tab_switches,
                'max' => $max,
                'force_stop' => false,
                'blocked' => $isBlocked || (($attempt->status instanceof ExamAttemptStatus ? $attempt->status : ExamAttemptStatus::tryFrom((string) $attempt->status)) === ExamAttemptStatus::Blocked),
                'message' => $isBlocked ? 'Ujian diblokir sementara. Menunggu keputusan guru/pengawas.' : null,
                'redirect' => null,
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("API Log Violation Failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function resultRedirectUrl(ExamAttempt $attempt): string
    {
        $attempt->loadMissing('exam:id');
        return route('student.results.detail', $attempt->id);
    }
}
