<?php

namespace App\Livewire\Teacher\Grading;

use App\Enums\ExamAttemptStatus;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class StudentList extends Component
{
    use \Livewire\WithPagination;

    public $examId;
    public $exam;
    public $search = '';
    public $classroomFilter = '';
    private function pageSessionKey(): string
    {
        return 'grading_page_exam_' . $this->examId;
    }
    
    // Publish logic (optional for now, maybe toggle exam visibility?)
    public function publish()
    {
        $this->exam->update(['is_published' => true]);
        $this->dispatch('notify', ['message' => 'Nilai ujian berhasil dipublikasikan!']);
    }

    public function mount($exam)
    {
        $this->examId = $exam;
        $this->classroomFilter = (string) request()->query('classroomFilter', '');
        $incomingPage = (int) request()->query('gradingPage', (int) session($this->pageSessionKey(), 1));
        if ($incomingPage > 1) {
            $this->setPage($incomingPage, 'gradingPage');
        }
        $this->exam = \App\Models\Exam::findOrFail($exam);
        Gate::authorize('grade', $this->exam);

        // Grading page is only for exams with essay questions.
        $hasEssay = $this->exam->questions()->where('type', 'essay')->exists();
        if (!$hasEssay) {
            $route = auth()->user()->isAdmin() ? 'admin.grading.index' : 'teacher.grading.index';
            return redirect()->route($route);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage('gradingPage');
        session([$this->pageSessionKey() => 1]);
    }

    public function updatingClassroomFilter()
    {
        $this->resetPage('gradingPage');
        session([$this->pageSessionKey() => 1]);
    }

    public function render()
    {
        $targetStatuses = [
            ExamAttemptStatus::Submitted->value, 
            ExamAttemptStatus::Graded->value,
            ExamAttemptStatus::Completed->value,
            ExamAttemptStatus::TimedOut->value,
            ExamAttemptStatus::Abandoned->value
        ];

        $attempts = \App\Models\ExamAttempt::where('exam_id', $this->examId)
            ->whereIn('status', $targetStatuses)
            ->with('student.user')
            ->when(filled($this->classroomFilter), function ($query) {
                $query->whereHas('student', function ($studentQuery) {
                    $studentQuery->where('classroom_id', (int) $this->classroomFilter);
                });
            })
            ->when(filled($this->search), function ($query) {
                $keyword = trim((string) $this->search);

                $query->whereHas('student', function ($studentQuery) use ($keyword) {
                    $studentQuery->where('nis', 'like', "%{$keyword}%")
                        ->orWhereHas('user', function ($userQuery) use ($keyword) {
                            $userQuery->where('name', 'like', "%{$keyword}%");
                        });
                });
            })
            // Latest first.
            ->latest('submitted_at')
            ->latest('updated_at')
            ->paginate(10, ['*'], 'gradingPage');

        // Persist and keep page valid after data changes (e.g., after grading/publish).
        if ($attempts->currentPage() > $attempts->lastPage() && $attempts->lastPage() > 0) {
            $targetPage = $attempts->lastPage();
            $this->setPage($targetPage, 'gradingPage');
            session([$this->pageSessionKey() => $targetPage]);
            $attempts = \App\Models\ExamAttempt::where('exam_id', $this->examId)
                ->whereIn('status', $targetStatuses)
                ->with('student.user')
                ->when(filled($this->classroomFilter), function ($query) {
                    $query->whereHas('student', function ($studentQuery) {
                        $studentQuery->where('classroom_id', (int) $this->classroomFilter);
                    });
                })
                ->when(filled($this->search), function ($query) {
                    $keyword = trim((string) $this->search);

                    $query->whereHas('student', function ($studentQuery) use ($keyword) {
                        $studentQuery->where('nis', 'like', "%{$keyword}%")
                            ->orWhereHas('user', function ($userQuery) use ($keyword) {
                                $userQuery->where('name', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->latest('submitted_at')
                ->latest('updated_at')
                ->paginate(10, ['*'], 'gradingPage');
        }

        session([$this->pageSessionKey() => $attempts->currentPage()]);

        $classrooms = $this->exam->classrooms()
            ->orderBy('name')
            ->get(['classrooms.id', 'classrooms.name']);

        return view('teacher.grading.student-list', [
            'attempts' => $attempts,
            'examName' => $this->exam->name,
            'className' => $classrooms->pluck('name')->join(', '),
            'classrooms' => $classrooms,
            'isPublished' => $this->exam->is_published
        ])->layout(\Illuminate\Support\Facades\Auth::user()->isAdmin() ? 'layouts.admin' : 'layouts.teacher')->title('Daftar Siswa - ' . $this->exam->name);
    }

}
