<?php

namespace App\Livewire\Common\Monitoring;

use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Detail extends Component
{
    use HasDynamicLayout;

    public $examId;

    public $search = '';
    public $filterStatus = '';
    public $filterClass = '';

    public function mount($id)
    {
        $this->examId = $id;
    }

    public function render()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $isAdmin = request()->is('admin/*');
        
        $exam = \App\Models\Exam::with(['subject', 'classrooms', 'attempts.student.user', 'attempts.answers'])
            ->findOrFail($this->examId);

        // Authorization Check
        if (!$isAdmin && $user->role === 'teacher') {
            if ($exam->teacher_id !== $user->teacher->id) {
                abort(403, 'Anda tidak memiliki akses ke ujian ini.');
            }
        }

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

        $students = $assignedStudents->map(function($student) use ($exam, $totalQuestions) {
            $attempt = $exam->attempts->where('student_id', $student->id)->first();
            
            $status = 'not_started';
            $ansCount = 0;
            $width = '0%';
            $tab_alert = 0;
            
            if ($attempt) {
                $status = $attempt->status;
                $tab_alert = $attempt->tab_switches;
                $ansCount = $attempt->answers->count();
                
                if ($totalQuestions > 0) {
                    $percent = ($ansCount / $totalQuestions) * 100;
                    $width = round($percent) . '%';
                }

                if ($status === 'completed' || $status === 'graded' || $status === 'submitted') {
                    $width = '100%';
                }
            }
            
            $progress = $ansCount . '/' . $totalQuestions;

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

        // Apply Status Filter (Collection level)
        if ($this->filterStatus) {
            $students = $students->filter(function ($student) {
                if ($this->filterStatus === 'working') {
                    return $student['status'] === 'in_progress';
                }
                if ($this->filterStatus === 'completed') {
                    return in_array($student['status'], ['completed', 'graded', 'submitted']);
                }
                if ($this->filterStatus === 'not_started') {
                    return $student['status'] === 'not_started';
                }
                return true;
            });
        }

        // Real Logs (from attempts sorted by recent update)
        $live_logs = \App\Models\ExamAttempt::where('exam_id', $this->examId)
            // Removed status filter to show ALL recent activity (start, progress, finish)
            ->with('student.user')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($attempt) {
                $activity = 'Sedang mengerjakan';
                $type = 'info';

                if ($attempt->tab_switches > 0) {
                    $activity = 'Terdeteksi pindah tab';
                    $type = 'warning';
                }

                if (in_array($attempt->status, ['submitted', 'completed', 'graded'])) {
                    $activity = 'Selesai Mengerjakan';
                    $type = 'success';
                }

                return [
                    'time' => $attempt->updated_at->format('H:i:s'),
                    'student' => $attempt->student->user->name,
                    'activity' => $activity,
                    'type' => $type
                ];
            });

        return $this->applyLayout('livewire.common.monitoring.detail', [
            'exam' => $exam,
            'students' => $students,
            'live_logs' => $live_logs,
            'backRoute' => $isAdmin ? 'admin.monitor' : 'teacher.monitoring',
            'classes' => $exam->classrooms->pluck('name')->unique() // Pass classes for filter dropdown
        ]);
    }

    public function forceSubmit($studentId)
    {
        $attempt = \App\Models\ExamAttempt::where('exam_id', $this->examId)
            ->where('student_id', $studentId)
            ->first();

        if ($attempt) {
            $attempt->update([
                'status' => 'submitted', // Or 'completed' depending on logic
                'submitted_at' => now()
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
}
