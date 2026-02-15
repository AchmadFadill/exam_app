<?php

namespace App\Livewire\Admin;

use App\Enums\ExamAttemptStatus;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public $live_logs = [];

    protected $listeners = [
        'echo:security-monitoring,.student-violation' => 'handleViolation',
        'manual-violation' => 'handleViolation' 
    ];

    public function handleViolation($event)
    {
        \Illuminate\Support\Facades\Log::info("⚡ [DASHBOARD] handleViolation Triggered", ['event' => $event]);

        // Unpack if wrapped (from manual dispatch)
        if (isset($event['event'])) {
             $event = $event['event'];
        }
        
        $statusType = 'info';
        if (in_array($event['violation_type'], ['tab_switch', 'fullscreen_exit'])) {
            $statusType = 'warning';
        }
        if ($event['violation_type'] === 'submit') {
            $statusType = 'success';
        }

        $newLog = [
            'id' => uniqid(),
            'timestamp' => $event['timestamp'],
            'time' => \Carbon\Carbon::parse($event['timestamp'])->format('H:i:s'),
            'student' => $event['student_name'],
            'exam' => \App\Models\Exam::find($event['exam_id'])->name ?? 'Ujian', // Added Exam Name for global context
            'activity' => $event['message'] ?? $event['violation_type'],
            'type' => $statusType
        ];

        array_unshift($this->live_logs, $newLog);
        $this->live_logs = array_slice($this->live_logs, 0, 20);
    }
    
    public function mount()
    {
        $this->loadInitialLogs();
    }

    public function loadInitialLogs()
    {
        // 1. Violation/Activity Logs (Global)
        $violation_logs = \App\Models\ExamActivity::with(['user', 'exam'])
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($activity) {
                $statusType = 'info';
                if ($activity->severity === 'warning') $statusType = 'warning';
                if ($activity->severity === 'critical') $statusType = 'danger';
                if ($activity->type === 'submit') $statusType = 'success';

                return [
                    'id' => 'act-' . $activity->id,
                    'timestamp' => $activity->created_at,
                    'time' => $activity->created_at->format('H:i:s'),
                    'student' => $activity->user->name,
                    'exam' => $activity->exam->name ?? '-',
                    'activity' => $activity->message ?? $activity->type,
                    'type' => $statusType
                ];
            });

        // 2. Progress Logs (ExamAttempts) - Global
        $progress_logs = \App\Models\ExamAttempt::with(['student.user', 'exam'])
            ->where('updated_at', '>=', now()->subHours(24)) // Last 24 hours
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($attempt) {
                // Determine status/activity text
                $activity = 'Melanjutkan pengerjaan';
                $type = 'info';

                // Check if just started (created within 10s of updated)
                if ($attempt->created_at->diffInSeconds($attempt->updated_at) < 10 && $attempt->status === ExamAttemptStatus::InProgress) {
                    $activity = 'Memulai ujian';
                    $type = 'primary';
                } elseif (($attempt->status instanceof ExamAttemptStatus ? $attempt->status : ExamAttemptStatus::tryFrom((string) $attempt->status))?->isFinalized()) {
                    $activity = 'Selesai Mengerjakan';
                    $type = 'success';
                }

                return [
                    'id' => 'att-' . $attempt->id . '-' . $attempt->updated_at->timestamp,
                    'timestamp' => $attempt->updated_at,
                    'time' => $attempt->updated_at->format('H:i:s'),
                    'student' => $attempt->student->user->name ?? 'Tidak Diketahui',
                    'exam' => $attempt->exam->name ?? '-',
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
        $today = now();

        // Global Stats
        $stats = [
            'total_students' => \App\Models\Student::count(),
            'total_teachers' => \App\Models\Teacher::count(),
            'total_exams' => \App\Models\Exam::count(),
            'total_questions' => \App\Models\Question::count(),
            'active_exams_count' => 0, // Will update below
        ];

        $quick_stats = [
            'exams_today' => \App\Models\Exam::whereDate('date', $today)->count(),
            'active_students' => \App\Models\ExamAttempt::query()
                ->where('status', ExamAttemptStatus::InProgress->value)
                ->distinct('student_id')
                ->count('student_id'),
            'pending_password_requests' => \App\Models\PasswordResetRequest::where('status', 'pending')->count(),
        ];

        // Active Exams Feed - REAL DATA - OPTIMIZED
        $activeExamsQuery = \App\Models\Exam::whereIn('status', ['scheduled', 'active', 'running'])
            ->whereDate('date', $today)
            ->whereTime('start_time', '<=', $today->format('H:i'))
            ->whereTime('end_time', '>=', $today->format('H:i'))
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->orderByDesc('id')
            ->with([
                'subject', 
                'classrooms' => function($q) {
                    $q->withCount('students');
                },
                'teacher.user', 
            ])
            ->withCount([
                'attempts as students_online_count' => function($q) {
                    $q->where('status', ExamAttemptStatus::InProgress->value);
                },
                'attempts as finished_count' => function($q) {
                    $q->whereIn('status', [
                        ExamAttemptStatus::Submitted->value,
                        ExamAttemptStatus::Graded->value,
                        ExamAttemptStatus::Completed->value,
                        ExamAttemptStatus::TimedOut->value,
                        ExamAttemptStatus::Abandoned->value,
                    ]);
                }
            ])
            ->paginate(3, ['*'], 'activeExamPage');

        $active_exams = $activeExamsQuery->through(function($exam) {
            // Calculate total students assigned to this exam (via classrooms) - OPTIMIZED
            $totalStudents = $exam->classrooms->sum('students_count');
            
            // Students currently in progress
            $studentsOnline = (int) ($exam->students_online_count ?? 0);
            
            // Dynamic progress based on student completion, not elapsed clock time.
            $finishedCount = (int) ($exam->finished_count ?? 0);
            $progress = $totalStudents > 0 ? (int) round(($finishedCount / $totalStudents) * 100) : 0;

            return [
                'id' => $exam->id,
                'status' => 'active',
                'name' => $exam->name,
                'subject' => $exam->subject->name ?? 'Mapel Tidak Diketahui',
                'class' => $exam->classrooms->pluck('name')->join(', '),
                'teacher' => $exam->teacher->user->name ?? 'Guru Tidak Diketahui',
                'progress' => $progress,
                'students_online' => $studentsOnline,
                'total_students' => $totalStudents,
                'monitor_url' => route('admin.monitor.detail', $exam->id),
            ];
        });

        // Update active exams count
        $stats['active_exams_count'] = $active_exams->total();

        return view('admin.dashboard', [
            'greeting' => $this->getGreeting(),
            'stats' => $stats,
            'quick_stats' => $quick_stats,
            'active_exams' => $active_exams,
            'live_logs' => $this->live_logs, // Updated variable
        ])->layout('layouts.admin', ['title' => 'Dashboard']);
    }

    private function getGreeting()
    {
        $hour = date('H');
        if ($hour < 11) return 'Selamat Pagi';
        if ($hour < 15) return 'Selamat Siang';
        if ($hour < 19) return 'Selamat Sore';
        return 'Selamat Malam';
    }
}
