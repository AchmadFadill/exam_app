<?php

namespace App\Livewire\Common\Monitoring;

use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Detail extends Component
{
    use HasDynamicLayout;

    public $examId;

    public function mount($id)
    {
        $this->examId = $id;
    }

    public function render()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $isAdmin = request()->is('admin/*');
        
        $exam = \App\Models\Exam::with(['subject', 'classrooms', 'attempts.student.user'])
            ->findOrFail($this->examId);

        // Authorization Check
        if (!$isAdmin && $user->role === 'teacher') {
            if ($exam->teacher_id !== $user->teacher->id) {
                abort(403, 'Anda tidak memiliki akses ke ujian ini.');
            }
        }

        // Real Student Data
        // Get all students from the assigned classrooms
        $assignedStudents = \App\Models\Student::whereIn('classroom_id', $exam->classrooms->pluck('id'))
            ->with('user', 'classroom')
            ->get();
            
        $students = $assignedStudents->map(function($student) use ($exam) {
            $attempt = $exam->attempts->where('student_id', $student->id)->first();
            
            $status = 'not_started';
            $progress = '0/' . $exam->questions()->count(); // Simplified
            $width = '0%';
            $tab_alert = 0;
            
            if ($attempt) {
                $status = $attempt->status;
                // Calculate progress roughly based on answered questions if possible, 
                // or just based on status. For now, we use status.
                $tab_alert = $attempt->tab_switches;
                
                if ($status === 'completed' || $status === 'graded') {
                    $width = '100%';
                } elseif ($status === 'in_progress') {
                    $width = '50%'; // Dynamic progress would need answer count
                }
            }

            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'class' => $student->classroom->name,
                'status' => $status,
                'progress' => $progress,
                'w' => $width,
                'tab_alert' => $tab_alert,
                'attempt_id' => $attempt ? $attempt->id : null
            ];
        });

        // Real Logs (from attempts sorted by recent update)
        $live_logs = \App\Models\ExamAttempt::where('exam_id', $this->examId)
            ->where('status', 'in_progress')
            ->with('student.user')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($attempt) {
                return [
                    'time' => $attempt->updated_at->format('H:i:s'),
                    'student' => $attempt->student->user->name,
                    'activity' => $attempt->tab_switches > 0 ? 'Terdeteksi pindah tab' : 'Sedang mengerjakan',
                    'type' => $attempt->tab_switches > 0 ? 'warning' : 'info'
                ];
            });

        return $this->applyLayout('livewire.common.monitoring.detail', [
            'exam' => $exam,
            'students' => $students,
            'live_logs' => $live_logs,
            'backRoute' => $isAdmin ? 'admin.monitor' : 'teacher.monitoring'
        ]);
    }

    public function forceSubmit($studentId)
    {
        $this->dispatch('notify', ['message' => 'Ujian siswa berhasil dihentikan (Simulasi)']);
    }
}
