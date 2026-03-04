<?php

namespace App\Livewire\Common\Monitoring;

use App\Enums\ExamAttemptStatus;
use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class Detail extends Component
{
    use HasDynamicLayout;

    public $examId;

    public $search = '';
    public $filterStatus = '';
    public $filterClass = '';

    public $live_logs = [];

    protected $listeners = ['echo:security-monitoring,.student-violation' => 'handleViolation'];

    public function handleViolation($event)
    {
        // Only update if the violation belongs to the current exam
        if ((int)$event['exam_id'] !== (int)$this->examId) {
            return;
        }

        $statusType = 'info';
        if (in_array($event['violation_type'], ['tab_switch', 'fullscreen_exit'])) {
            $statusType = 'warning';
        } elseif ($event['violation_type'] === 'blocked') {
            $statusType = 'danger';
        }

        $newLog = [
            'id' => uniqid(),
            'timestamp' => $event['timestamp'],
            'time' => \Carbon\Carbon::parse($event['timestamp'])->format('H:i:s'),
            'student' => $event['student_name'],
            'activity' => $event['message'] ?? $event['violation_type'],
            'type' => $statusType
        ];

        // Add to logs
        array_unshift($this->live_logs, $newLog);
        $this->live_logs = array_slice($this->live_logs, 0, 20);
    }

    public function mount($id)
    {
        $this->examId = $id;
        $this->loadInitialLogs();
    }

    public function loadInitialLogs()
    {
         // 1. Violation Logs (from ExamActivity)
        $violation_logs = \App\Models\ExamActivity::where('exam_id', $this->examId)
            ->where('created_at', '>=', now()->subHours(24))
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function($activity) {
                $statusType = 'info';
                if ($activity->severity === 'warning') $statusType = 'warning';
                if ($activity->severity === 'critical') $statusType = 'danger';
                if ($activity->type === 'submit') $statusType = 'success';
                if ($activity->type === 'resume') $statusType = 'primary';

                return [
                    'id' => $activity->id,
                    'timestamp' => $activity->created_at,
                    'time' => $activity->created_at->format('H:i:s'),
                    'student' => $activity->user->name,
                    'activity' => $activity->message ?? $activity->type,
                    'type' => $statusType
                ];
            });

        // 2. Progress Logs (from ExamAttempts updated recently)
        $progress_logs = \App\Models\ExamAttempt::where('exam_id', $this->examId)
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->with('student.user')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($attempt) {
                $activity = 'Melanjutkan pengerjaan';
                $type = 'info';

                if ($attempt->created_at->diffInSeconds($attempt->updated_at) < 5) {
                    $activity = 'Memulai ujian';
                    $type = 'primary';
                } elseif (($attempt->status instanceof ExamAttemptStatus ? $attempt->status : ExamAttemptStatus::tryFrom((string) $attempt->status))?->isFinalized()) {
                    $activity = 'Selesai Mengerjakan';
                    $type = 'success';
                }

                return [
                    'timestamp' => $attempt->updated_at,
                    'time' => $attempt->updated_at->format('H:i:s'),
                    'student' => $attempt->student->user->name,
                    'activity' => $activity,
                    'type' => $type
                ];
            });

        // 3. Merge and Sort
        $this->live_logs = collect($violation_logs)->merge($progress_logs)
            ->sortByDesc('timestamp')
            ->take(20)
            ->values()
            ->toArray();
    }

    public function render()
    {
        $isAdmin = Auth::user()?->isAdmin() ?? false;
        
        $exam = \App\Models\Exam::with(['subject', 'classrooms' => function($query) {
            $query->withCount('students');
        }, 'attempts.student.user', 'attempts.answers'])
            ->findOrFail($this->examId);
        Gate::authorize('view', $exam);

        // Real Student Data
        // Get all students from the assigned classrooms
        $assignedStudentsQuery = \App\Models\Student::whereIn('classroom_id', $exam->classrooms->pluck('id'))
            ->with('user', 'classroom');

        // Apply Search Filter
        if ($this->search) {
            $assignedStudentsQuery->whereHas('user', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        // Apply Class Filter
        if ($this->filterClass) {
            $assignedStudentsQuery->whereHas('classroom', function($q) {
                $q->where('name', $this->filterClass);
            });
        }

        $assignedStudents = $assignedStudentsQuery->get();
            
        $totalQuestions = $exam->questions()->count();
        $questionTypeMap = $exam->questions->pluck('type', 'id');

        $students = $assignedStudents->map(function($student) use ($exam, $totalQuestions, $isAdmin, $questionTypeMap) {
            $attempt = $exam->attempts->where('student_id', $student->id)->first();
            
            $status = 'not_started';
            $ansCount = 0;
            $width = '0%';
            $tab_alert = 0;
            
            if ($attempt) {
                $status = $attempt->status;
                $tab_alert = $attempt->tab_switches;

                // Count only genuinely answered questions.
                // MC: selected_option_id exists.
                // Essay: non-empty text answer.
                $ansCount = $attempt->answers
                    ->filter(function ($answer) use ($questionTypeMap) {
                        $questionType = $questionTypeMap->get($answer->question_id);

                        if ($questionType === 'multiple_choice') {
                            $raw = trim((string) ($answer->answer ?? ''));
                            return !is_null($answer->selected_option_id)
                                || ($raw !== '' && is_numeric($raw));
                        }

                        if ($questionType === 'essay') {
                            return trim((string) ($answer->answer ?? '')) !== '';
                        }

                        return !is_null($answer->selected_option_id) || trim((string) ($answer->answer ?? '')) !== '';
                    })
                    ->count();
                
                if ($totalQuestions > 0) {
                    $percent = ($ansCount / $totalQuestions) * 100;
                    $width = round($percent) . '%';
                }
            }
            
            $progress = $ansCount . '/' . $totalQuestions;

            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'class' => $student->classroom->name,
                'status' => $status instanceof ExamAttemptStatus ? $status->value : $status,
                'progress' => $progress,
                'w' => $width,
                'tab_alert' => $tab_alert,
                'attempt_id' => $attempt ? $attempt->id : null,
                'detail_route' => $isAdmin 
                    ? 'admin.reports.student' 
                    : 'teacher.reports.student'
            ];
        });

        // Apply Status Filter (Collection level)
        if ($this->filterStatus) {
            $students = $students->filter(function ($student) {
                if ($this->filterStatus === 'working') {
                    return $student['status'] === ExamAttemptStatus::InProgress->value;
                }
                if ($this->filterStatus === 'blocked') {
                    return $student['status'] === ExamAttemptStatus::Blocked->value;
                }
                if ($this->filterStatus === 'completed') {
                    $status = ExamAttemptStatus::tryFrom((string) $student['status']);
                    return $status?->isFinalized() ?? false;
                }
                if ($this->filterStatus === 'not_started') {
                    return $student['status'] === 'not_started';
                }
                return true;
            });
        }

        // Logs loaded in mount/handleViolation


        return $this->applyLayout('livewire.common.monitoring.detail', [
            'exam' => $exam,
            'students' => $students,
            'live_logs' => $this->live_logs,
            'backRoute' => $isAdmin ? 'admin.monitor' : 'teacher.monitoring',
            'classes' => $exam->classrooms->pluck('name')->unique() // Pass classes for filter dropdown
        ]);
    }

    public function forceSubmit($studentId)
    {
        $exam = \App\Models\Exam::with(['questions.options', 'teacher.user'])->findOrFail($this->examId);
        Gate::authorize('grade', $exam);

        $attempt = \App\Models\ExamAttempt::where('exam_id', $this->examId)
            ->where('student_id', $studentId)
            ->first();

        if ($attempt && !$attempt->submitted_at) {
            $attempt = app(ProcessExamSubmissionAction::class)->execute($exam, $attempt, [], function ($lockedAttempt) {
                $lockedAttempt->update([
                    'teacher_notes' => trim(trim((string) $lockedAttempt->teacher_notes) . ' Force-submitted by teacher after security block.'),
                ]);
            }, ExamAttemptStatus::Submitted);

            \App\Models\ExamActivity::create([
                'user_id' => Auth::id(),
                'exam_id' => $this->examId,
                'exam_attempt_id' => $attempt->id,
                'type' => 'submit',
                'severity' => 'critical',
                'message' => 'Ujian diakhiri oleh guru/pengawas setelah pelanggaran.',
                'metadata' => [
                    'student_id' => $studentId,
                ],
            ]);
            
            // Send Notification
            try {
                $teacher = $exam->teacher->user;
                $teacher->notify(new \App\Notifications\ExamSubmitted($exam, $attempt->student->user));
            } catch (\Exception $e) {}
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Ujian siswa berhasil dihentikan secara paksa.'
            ]);
        } else {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Siswa belum memulai ujian.'
            ]);
        }
    }

    public function resumeAttempt($studentId)
    {
        $exam = \App\Models\Exam::findOrFail($this->examId);
        Gate::authorize('grade', $exam);

        $attempt = \App\Models\ExamAttempt::where('exam_id', $this->examId)
            ->where('student_id', $studentId)
            ->first();

        if (!$attempt) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Siswa belum memulai ujian.'
            ]);
            return;
        }

        $attempt->update([
            'status' => ExamAttemptStatus::InProgress,
            'tab_switches' => 0,
            'teacher_notes' => trim(trim((string) $attempt->teacher_notes) . ' Resumed by teacher; violation counter reset.'),
        ]);

        \App\Models\ExamActivity::create([
            'user_id' => Auth::id(),
            'exam_id' => $this->examId,
            'exam_attempt_id' => $attempt->id,
            'type' => 'resume',
            'severity' => 'info',
            'message' => 'Ujian dilanjutkan kembali oleh guru/pengawas. Counter pelanggaran direset ke 0.',
            'metadata' => [
                'student_id' => $studentId,
                'reset_tab_switches' => true,
            ],
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Siswa diizinkan melanjutkan ujian. Counter pelanggaran direset.'
        ]);
    }

}
