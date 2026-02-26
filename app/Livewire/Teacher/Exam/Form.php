<?php

namespace App\Livewire\Teacher\Exam;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Form extends Component
{
    public $examId = null;
    public $currentStep = 1;
    public $step = 1; // Alias for currentStep (used in template)

    // Step 1: Basic Information
    public $name = '';
    public $subject_id = '';
    public $date = '';
    public $start_time = '';
    public $end_time = '';
    public $duration_minutes = 90;
    public $passing_grade = 70;
    public $default_score = 10;

    // Step 2: Question Selection
    public $selectedQuestions = [];
    public $questionScores = [];
    public $searchQuery = '';
    public $filterSubject = '';
    public $filterType = '';

    // Step 3: Class Assignment & Settings
    public $selectedClasses = [];
    public $classes = []; // Alias for selectedClasses (used in template)
    public $token = '';
    public $shuffle_questions = false;
    public $shuffle_answers = false;
    public $enable_tab_tolerance = false;
    public $tab_tolerance = 3;
    public $show_score_to_student = true;
    public $show_answers_to_student = true;

    // Step 4: Status
    public $status = 'draft';
    public $teacherSubjectIds = [];

    // Instant Question Modal


    public function mount($id = null)
    {
        if (Auth::user()->isTeacher()) {
            $teacher = \App\Models\Teacher::with('subjects:id')->where('user_id', Auth::id())->first();
            $this->teacherSubjectIds = $teacher
                ? $teacher->subjects->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
                : [];
        }

        if ($id) {
            $this->examId = $id;
            $this->loadExam($id);
        } else {
            // Initialize default values with current date and time (timezone-aware)
            $now = now(); // Uses Laravel's configured timezone
            $this->date = $now->format('Y-m-d');
            $this->start_time = $now->format('H:i');
            // Set end time to current time + default duration (90 minutes)
            $this->end_time = $now->addMinutes($this->duration_minutes)->format('H:i');
            $this->generateToken();
            
            // Set default subject for teacher
            if (Auth::user()->isTeacher() && !empty($this->teacherSubjectIds)) {
                // For teacher flow, subject is locked to the assigned subject.
                $this->subject_id = (int) $this->teacherSubjectIds[0];
            }
        }
        
        // Initialize $classes to match $selectedClasses
        $this->classes = $this->selectedClasses;
    }

    // Keep $classes and $selectedClasses synchronized
    public function updatedClasses($value)
    {
        $this->selectedClasses = $value;
    }

    public function updatedSelectedClasses($value)
    {
        $this->classes = $value;
    }

    public function updatedStartTime($value)
    {
        if ($this->duration_minutes && $value) {
            try {
                // Parse start time and add duration (cast to int for Carbon)
                $start = \Carbon\Carbon::createFromFormat('H:i', $value);
                $this->end_time = $start->addMinutes((int) $this->duration_minutes)->format('H:i');
            } catch (\Exception $e) {
                // Ignore parsing errors
            }
        }
    }

    public function updatedDurationMinutes($value)
    {
        if ($this->start_time && $value) {
            try {
                // Parse start time and add duration (cast to int for Carbon)
                $start = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
                $this->end_time = $start->addMinutes((int) $value)->format('H:i');
            } catch (\Exception $e) {
                // Ignore parsing errors
            }
        }
    }

    public function updatedSubjectId($value): void
    {
        if (Auth::user()->isTeacher() && !in_array((int) $value, $this->teacherSubjectIds, true)) {
            $this->subject_id = !empty($this->teacherSubjectIds) ? (int) $this->teacherSubjectIds[0] : '';
        }

        // Prevent stale mixed selection when subject is changed in step 1.
        $this->selectedQuestions = [];
        $this->questionScores = [];
    }

    protected function loadExam($id)
    {
        $exam = Exam::with(['questions', 'classrooms'])->findOrFail($id);
        Gate::authorize('update', $exam);

        $this->name = $exam->name;
        $this->subject_id = $exam->subject_id;
        $this->date = $exam->date->format('Y-m-d');
        $this->start_time = $exam->start_time;
        $this->end_time = $exam->end_time;
        $this->duration_minutes = $exam->duration_minutes;
        $this->passing_grade = $exam->passing_grade;
        $this->default_score = $exam->default_score;
        $this->token = $exam->token;
        $this->shuffle_questions = $exam->shuffle_questions;
        $this->shuffle_answers = $exam->shuffle_answers;
        $this->enable_tab_tolerance = $exam->enable_tab_tolerance;
        $this->tab_tolerance = $exam->tab_tolerance;
        $this->show_score_to_student = (bool) ($exam->show_score_to_student ?? true);
        $this->show_answers_to_student = (bool) ($exam->show_answers_to_student ?? true);
        $this->status = $exam->status;

        // Load selected questions with scores
        foreach ($exam->questions as $question) {
            $this->selectedQuestions[] = $question->id;
            $this->questionScores[$question->id] = $question->pivot->score;
        }

        // Load selected classes
        $this->selectedClasses = $exam->classrooms->pluck('id')->toArray();
    }

    // Computed property to expose $step for template compatibility
    public function getStepProperty()
    {
        return $this->currentStep;
    }

    public function generateToken()
    {
        do {
            $this->token = Exam::generateToken();
        } while (Exam::where('token', $this->token)->where('id', '!=', $this->examId)->exists());
    }

    public function regenerateToken()
    {
        $this->generateToken();
    }

    public function nextStep()
    {
        $this->validateCurrentStep();
        
        if ($this->currentStep < 2) {
            $this->currentStep++;
            $this->step = $this->currentStep;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->step = $this->currentStep;
        }
    }

    public function toggleLevel($level)
    {
        // Get all classrooms for this level (e.g., "X", "XI", "XII")
        // Add space after level to prevent "X" from matching "XI" or "XII"
        $levelClasses = Classroom::where('name', 'like', $level . ' %')->pluck('id')->toArray();
        
        // Check if all classes of this level are already selected
        $allSelected = !empty($levelClasses) && count(array_intersect($levelClasses, $this->classes)) === count($levelClasses);
        
        if ($allSelected) {
            // Deselect all classes of this level
            $this->classes = array_values(array_diff($this->classes, $levelClasses));
            $this->selectedClasses = $this->classes;
        } else {
            // Select all classes of this level
            $this->classes = array_values(array_unique(array_merge($this->classes, $levelClasses)));
            $this->selectedClasses = $this->classes;
        }
    }

    public function isGroupSelected($title, $subjectId)
    {
        $groupQuestions = Question::query()
            ->when(Auth::user()->isTeacher(), function ($q) {
                $q->where('teacher_id', $this->currentTeacherId());
            })
            ->where('title', $title)
            ->where('subject_id', $subjectId)
            ->pluck('id')
            ->toArray();
        
        return !empty($groupQuestions) && count(array_intersect($groupQuestions, $this->selectedQuestions)) === count($groupQuestions);
    }

    protected function validateCurrentStep()
    {
        $subjectRule = Auth::user()->isTeacher()
            ? 'required|in:' . implode(',', $this->teacherSubjectIds)
            : 'required|exists:subjects,id';

        $rules = match ($this->currentStep) {
            1 => [
                'name' => 'required|string|max:255',
                'subject_id' => $subjectRule,
                'date' => 'required|date',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
                'duration_minutes' => 'required|integer|min:10|max:300',
                'passing_grade' => 'required|integer|min:0|max:100',
                'default_score' => 'required|integer|min:1',
                'classes' => 'required|array|min:1',
                'token' => 'required|string|size:6|unique:exams,token,' . $this->examId,
                'tab_tolerance' => 'required|integer|min:0|max:10',
                'show_score_to_student' => 'boolean',
                'show_answers_to_student' => 'boolean',
            ],
            2 => [
                'selectedQuestions' => 'required|array|min:1',
                'questionScores.*' => 'required|integer|min:1',
            ],
            default => [],
        };

        $this->validate($rules);

        if ($this->currentStep === 2) {
            $this->normalizeSelectedQuestionsToSingleGroup();
        }

        if ($this->currentStep === 2 && !$this->hasSingleQuestionGroup()) {
            // Do not block save with validation error; selection is normalized
            // to a single group by normalizeSelectedQuestionsToSingleGroup().
            $this->normalizeSelectedQuestionsToSingleGroup();
        }
    }



    public function toggleQuestion($questionId)
    {
        $question = Question::query()
            ->when(Auth::user()->isTeacher(), function ($q) {
                $q->where('teacher_id', $this->currentTeacherId());
            })
            ->select('id', 'score', 'title', 'subject_id')
            ->find($questionId);
        if (!$question) {
            return;
        }

        if (in_array($questionId, $this->selectedQuestions)) {
            // Remove question
            $this->selectedQuestions = array_values(array_diff($this->selectedQuestions, [$questionId]));
            unset($this->questionScores[$questionId]);
        } else {
            $selectedGroup = $this->getSelectedGroupKey();
            $candidateGroup = $this->buildGroupKey($question->title, (int) $question->subject_id);

            if ($selectedGroup !== null && $selectedGroup !== $candidateGroup) {
                // Switch group automatically: keep only the newly selected question.
                $this->selectedQuestions = [];
                $this->questionScores = [];
            }

            // Add question with its own configured score from bank soal
            $this->selectedQuestions[] = $questionId;
            $this->questionScores[$questionId] = $question?->score ?? $this->default_score;
        }
    }

    public function toggleQuestionGroup($title, $subjectId)
    {
        // Get all questions with this title and subject (including score)
        $groupQuestions = Question::query()
            ->when(Auth::user()->isTeacher(), function ($q) {
                $q->where('teacher_id', $this->currentTeacherId());
            })
            ->where('title', $title)
            ->where('subject_id', $subjectId)
            ->get(['id', 'score']);

        $groupQuestionIds = $groupQuestions->pluck('id')->toArray();
        
        // Check if all questions in group are already selected
        $allSelected = !empty($groupQuestionIds) && count(array_intersect($groupQuestionIds, $this->selectedQuestions)) === count($groupQuestionIds);
        
        if ($allSelected) {
            // Remove all questions from this group
            $this->selectedQuestions = array_values(array_diff($this->selectedQuestions, $groupQuestionIds));
            foreach ($groupQuestionIds as $qId) {
                unset($this->questionScores[$qId]);
            }
        } else {
            // In single-group mode, selecting a group always replaces previous selection.
            $this->selectedQuestions = [];
            $this->questionScores = [];
            foreach ($groupQuestions as $question) {
                $qId = $question->id;
                $this->selectedQuestions[] = $qId;
                $this->questionScores[$qId] = $question->score ?? $this->default_score;
            }
        }
    }

    public function saveExam()
    {
        // Keep aliases synchronized before validation/save.
        $this->selectedClasses = $this->classes;

        if (Auth::user()->isTeacher()) {
            if (empty($this->teacherSubjectIds)) {
                $this->dispatch('notify', [
                    'message' => 'Akun guru belum memiliki mata pelajaran yang diampu.',
                    'type' => 'error',
                ]);
                return;
            }
            if (!in_array((int) $this->subject_id, $this->teacherSubjectIds, true)) {
                $this->subject_id = (int) $this->teacherSubjectIds[0];
            }
        }

        // Validate all visible steps in this form (Step 1 and Step 2).
        $originalStep = $this->currentStep;
        $this->currentStep = 1;
        $this->validateCurrentStep();
        $this->currentStep = 2;
        $this->validateCurrentStep();
        $this->currentStep = $originalStep;
        $this->step = $this->currentStep;

        try {
            DB::beginTransaction();

            $user = Auth::user();
            
            // Get teacher ID properly for both teacher and admin users
            $teacherId = null;
            if ($user->isAdmin()) {
                // For admin, use the first teacher or create one
                $teacher = \App\Models\Teacher::first();
                $teacherId = $teacher ? $teacher->id : null;
            } elseif ($user->isTeacher()) {
                // For teacher, find their teacher record
                $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
                $teacherId = $teacher ? $teacher->id : null;
            }

            if (!$teacherId) {
                throw new \Exception('Teacher ID not found. Please ensure you have a teacher profile.');
            }

            // Create or update exam
            $examData = [
                'teacher_id' => $teacherId,
                'subject_id' => $this->subject_id,
                'name' => $this->name,
                'date' => $this->date,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'duration_minutes' => $this->duration_minutes,
                'token' => $this->token,
                'passing_grade' => $this->passing_grade,
                'default_score' => $this->default_score,
                'shuffle_questions' => $this->shuffle_questions,
                'shuffle_answers' => $this->shuffle_answers,
                'enable_tab_tolerance' => $this->enable_tab_tolerance,
                'tab_tolerance' => $this->tab_tolerance,
                'show_score_to_student' => (bool) $this->show_score_to_student,
                'show_answers_to_student' => (bool) $this->show_answers_to_student,
                'status' => 'scheduled', // Force scheduled status
            ];

            if ($this->examId) {
                $exam = Exam::findOrFail($this->examId);
                Gate::authorize('update', $exam);
                $exam->update($examData);
            } else {
                $exam = Exam::create($examData);
            }

            // Sync questions with scores and order
            $questionsData = [];
            foreach ($this->selectedQuestions as $index => $questionId) {
                $questionsData[$questionId] = [
                    'order' => $index + 1,
                    'score' => $this->questionScores[$questionId] ?? $this->default_score,
                ];
            }
            $exam->questions()->sync($questionsData);

            // Sync classrooms
            $exam->classrooms()->sync($this->selectedClasses);

            DB::commit();

            $this->dispatch('notify', [
                'message' => $this->examId ? 'Ujian berhasil diperbarui!' : 'Ujian berhasil dibuat!',
            ]);



            if ($user->isAdmin()) {
                return redirect()->route('admin.exams');
            }

            return redirect()->route('teacher.exams.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'message' => 'Gagal menyimpan ujian: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    public function saveDraft()
    {
        $this->status = 'draft';
        $this->saveExam();
    }



    protected $listeners = [
        'question-saved' => 'handleInstantQuestionSaved'
    ];

    public function handleInstantQuestionSaved($questionId)
    {
        $question = \App\Models\Question::select('id', 'title', 'subject_id', 'score')->find($questionId);
        if (!$question) {
            return;
        }

        $selectedGroup = $this->getSelectedGroupKey();
        $candidateGroup = $this->buildGroupKey($question->title, (int) $question->subject_id);

        if ($selectedGroup !== null && $selectedGroup !== $candidateGroup) {
            // Switch group to the newly created instant question.
            $this->selectedQuestions = [];
            $this->questionScores = [];
        }

        // Automatically select the new question for this exam
        $this->selectedQuestions[] = $questionId;
        
        // Get score from question
        $this->questionScores[$questionId] = $question->score ?? $this->default_score;

        $this->dispatch('notify', ['message' => 'Soal instan berhasil dibuat dan dipilih!']);
    }

    public function openInstantQuestionModal()
    {
        $this->dispatch('openQuestionModal', [
            'subject_id' => $this->subject_id,
            'title' => 'Soal Instan - ' . now()->format('H:i')
        ]);
    }

    public function publish()
    {
        $this->status = 'scheduled';
        $this->saveExam();
    }

    public function render()
    {
        $subjects = Auth::user()->isTeacher()
            ? Subject::query()->whereIn('id', $this->teacherSubjectIds)->orderBy('name')->get()
            : Subject::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();
        
        // Group questions by title for selection
        $questionGroups = Question::with(['subject'])
            ->when($this->searchQuery, function ($q) {
                $q->where('title', 'like', '%' . $this->searchQuery . '%');
            })
            ->when(Auth::user()->isTeacher(), function ($q) {
                $q->where('teacher_id', $this->currentTeacherId());
            })
            ->when(Auth::user()->isTeacher() && !empty($this->teacherSubjectIds), function ($q) {
                $q->whereIn('subject_id', $this->teacherSubjectIds);
            })
            // If subject_id is set (from Step 1), force filter by it.
            // Otherwise fallback to the manual filter (though typically subject_id is required in Step 1)
            ->when($this->subject_id, function($q) {
                $q->where('subject_id', $this->subject_id);
            }, function($q) {
                // If no subject_id set (shouldn't happen in Step 2), use manual filter
                return $q->when($this->filterSubject, function ($subQ) {
                    $subQ->where('subject_id', $this->filterSubject);
                });
            })
            ->when($this->filterType, function ($q) {
                $q->where('type', $this->filterType);
            })
            ->select(
                'title',
                'subject_id',
                \DB::raw('COUNT(*) as question_count'),
                \DB::raw('MIN(id) as first_id'),
                \DB::raw('COALESCE(SUM(score), 0) as total_points')
            )
            ->groupBy('title', 'subject_id')
            ->orderBy('title')
            ->get();

        return view('teacher.exam.form', [
            'subjects' => $subjects,
            'availableClasses' => $classrooms,
            'questionGroups' => $questionGroups,
        ])->layout(\Illuminate\Support\Facades\Auth::user()->isAdmin() ? 'layouts.admin' : 'layouts.teacher')->title($this->examId ? 'Edit Ujian' : 'Buat Ujian Baru');
    }

    private function hasSingleQuestionGroup(): bool
    {
        if (empty($this->selectedQuestions)) {
            return true;
        }

        $distinctGroupCount = Question::query()
            ->when(Auth::user()->isTeacher(), function ($q) {
                $q->where('teacher_id', $this->currentTeacherId());
            })
            ->whereIn('id', $this->selectedQuestions)
            ->select('title', 'subject_id')
            ->distinct()
            ->count();

        return $distinctGroupCount <= 1;
    }

    private function normalizeSelectedQuestionsToSingleGroup(): void
    {
        $selectedIds = collect($this->selectedQuestions)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            $this->selectedQuestions = [];
            $this->questionScores = [];
            return;
        }

        $questions = Question::query()
            ->when(Auth::user()->isTeacher(), function ($q) {
                $q->where('teacher_id', $this->currentTeacherId());
            })
            ->whereIn('id', $selectedIds->all())
            ->select('id', 'title', 'subject_id', 'score')
            ->get();

        if ($questions->isEmpty()) {
            $this->selectedQuestions = [];
            $this->questionScores = [];
            return;
        }

        $grouped = $questions->groupBy(fn ($q) => $this->buildGroupKey((string) $q->title, (int) $q->subject_id));
        if ($grouped->count() <= 1) {
            $this->selectedQuestions = $questions->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            return;
        }

        $targetGroup = $grouped
            ->sortByDesc(function ($items, $groupKey) {
                $countScore = $items->count() * 1000;
                $subjectBonus = ((int) $items->first()->subject_id === (int) $this->subject_id) ? 1 : 0;
                return $countScore + $subjectBonus;
            })
            ->keys()
            ->first();

        $kept = $grouped->get($targetGroup, collect())->values();
        $this->selectedQuestions = $kept->pluck('id')->map(fn ($id) => (int) $id)->all();

        $newScores = [];
        foreach ($kept as $question) {
            $qid = (int) $question->id;
            $newScores[$qid] = (int) ($this->questionScores[$qid] ?? $question->score ?? $this->default_score);
        }
        $this->questionScores = $newScores;
    }

    private function getSelectedGroupKey(): ?string
    {
        if (empty($this->selectedQuestions)) {
            return null;
        }

        $question = Question::query()
            ->when(Auth::user()->isTeacher(), function ($q) {
                $q->where('teacher_id', $this->currentTeacherId());
            })
            ->whereIn('id', $this->selectedQuestions)
            ->select('title', 'subject_id')
            ->first();

        if (!$question) {
            return null;
        }

        return $this->buildGroupKey($question->title, (int) $question->subject_id);
    }

    private function buildGroupKey(string $title, int $subjectId): string
    {
        return $subjectId . '::' . $title;
    }

    private function currentTeacherId(): ?int
    {
        if (!Auth::user()->isTeacher()) {
            return null;
        }

        return \App\Models\Teacher::where('user_id', Auth::id())->value('id');
    }
}
