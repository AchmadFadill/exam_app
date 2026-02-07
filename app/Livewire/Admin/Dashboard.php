<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class Dashboard extends Component
{
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
                if ($attempt->created_at->diffInSeconds($attempt->updated_at) < 10 && $attempt->status === 'in_progress') {
                    $activity = 'Memulai ujian';
                    $type = 'primary';
                } elseif (in_array($attempt->status, ['submitted', 'completed', 'graded'])) {
                    $activity = 'Selesai Mengerjakan';
                    $type = 'success';
                }

                return [
                    'id' => 'att-' . $attempt->id . '-' . $attempt->updated_at->timestamp,
                    'timestamp' => $attempt->updated_at,
                    'time' => $attempt->updated_at->format('H:i:s'),
                    'student' => $attempt->student->user->name ?? 'Unknown',
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
        // Global Stats
        $stats = [
            'total_students' => \App\Models\Student::count(),
            'total_teachers' => \App\Models\Teacher::count(),
            'total_exams' => \App\Models\Exam::count(),
            'total_questions' => \App\Models\Question::count(),
            'active_exams_count' => 0, // Will update below
        ];

        // System Health (Simulated/Partial Real)
        $diskFree = disk_free_space(base_path());
        $diskTotal = disk_total_space(base_path());
        $diskUsage = round((($diskTotal - $diskFree) / $diskTotal) * 100);
        
        $system_health = [
            'cpu_load' => rand(10, 40), // Simulated
            'ram_usage' => rand(30, 60), // Simulated
            'disk_space' => $diskUsage,
            'uptime' => 'Online', // Simplified
            'status' => 'Healthy',
        ];

        // Active Exams Feed - REAL DATA - OPTIMIZED
        $activeExamsQuery = \App\Models\Exam::where('status', 'scheduled')
            ->whereDate('date', now())
            ->whereTime('start_time', '<=', now()->format('H:i'))
            ->whereTime('end_time', '>=', now()->format('H:i'))
            ->with([
                'subject', 
                'classrooms' => function($q) {
                    $q->withCount('students');
                },
                'teacher.user', 
                'attempts' => function($q) {
                    $q->whereNotNull('started_at');
                }
            ])
            ->get();

        $active_exams = $activeExamsQuery->map(function($exam) {
            // Calculate total students assigned to this exam (via classrooms) - OPTIMIZED
            $totalStudents = $exam->classrooms->sum('students_count');
            
            // Students who have started (have an attempt record)
            $studentsOnline = $exam->attempts->count();
            
            // Calculate progress based on time elapsed
            $start = \Carbon\Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->start_time);
            $end = \Carbon\Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->end_time);
            $totalDuration = $start->diffInMinutes($end);
            $elapsed = $start->diffInMinutes(now());
            $progress = $totalDuration > 0 ? min(100, round(($elapsed / $totalDuration) * 100)) : 0;

            return [
                'subject' => $exam->subject->name ?? 'Unknown Subject',
                'class' => $exam->classrooms->pluck('name')->join(', '),
                'teacher' => $exam->teacher->user->name ?? 'Unknown Teacher',
                'progress' => $progress,
                'students_online' => $studentsOnline,
                'total_students' => $totalStudents,
            ];
        })->toArray();

        // Update active exams count
        $stats['active_exams_count'] = count($active_exams);

        return view('admin.dashboard', [
            'greeting' => $this->getGreeting(),
            'stats' => $stats,
            'system_health' => $system_health,
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
