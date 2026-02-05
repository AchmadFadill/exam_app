<?php

namespace App\Livewire\Teacher\Exam;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    // Step 4: Status
    public $status = 'draft';

    public function mount($id = null)
    {
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

    protected function loadExam($id)
    {
        $exam = Exam::with(['questions', 'classrooms'])->findOrFail($id);

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
        
        if ($this->currentStep < 4) {
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
        $groupQuestions = Question::where('title', $title)
            ->where('subject_id', $subjectId)
            ->pluck('id')
            ->toArray();
        
        return !empty($groupQuestions) && count(array_intersect($groupQuestions, $this->selectedQuestions)) === count($groupQuestions);
    }

    protected function validateCurrentStep()
    {
        $rules = match ($this->currentStep) {
            1 => [
                'name' => 'required|string|max:255',
                'subject_id' => 'required|exists:subjects,id',
                'date' => 'required|date',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
                'duration_minutes' => 'required|integer|min:1|max:300', // TODO: Restore min:10 after testing
                'passing_grade' => 'required|integer|min:0|max:100',
                'default_score' => 'required|integer|min:1',
            ],
            2 => [
                'selectedQuestions' => 'required|array|min:1',
                'questionScores.*' => 'required|integer|min:1',
            ],
            3 => [
                'selectedClasses' => 'required|array|min:1',
                'token' => 'required|string|size:6|unique:exams,token,' . $this->examId,
                'tab_tolerance' => 'required|integer|min:0|max:10',
            ],
            default => [],
        };

        $this->validate($rules);
    }

    public function toggleQuestion($questionId)
    {
        if (in_array($questionId, $this->selectedQuestions)) {
            // Remove question
            $this->selectedQuestions = array_values(array_diff($this->selectedQuestions, [$questionId]));
            unset($this->questionScores[$questionId]);
        } else {
            // Add question with default score
            $this->selectedQuestions[] = $questionId;
            $this->questionScores[$questionId] = $this->default_score;
        }
    }

    public function toggleQuestionGroup($title, $subjectId)
    {
        // Get all questions with this title and subject
        $groupQuestions = Question::where('title', $title)
            ->where('subject_id', $subjectId)
            ->pluck('id')
            ->toArray();
        
        // Check if all questions in group are already selected
        $allSelected = !empty($groupQuestions) && count(array_intersect($groupQuestions, $this->selectedQuestions)) === count($groupQuestions);
        
        if ($allSelected) {
            // Remove all questions from this group
            $this->selectedQuestions = array_values(array_diff($this->selectedQuestions, $groupQuestions));
            foreach ($groupQuestions as $qId) {
                unset($this->questionScores[$qId]);
            }
        } else {
            // Add all questions from this group
            foreach ($groupQuestions as $qId) {
                if (!in_array($qId, $this->selectedQuestions)) {
                    $this->selectedQuestions[] = $qId;
                    $this->questionScores[$qId] = $this->default_score;
                }
            }
        }
    }

    public function saveExam()
    {
        // Validate all steps
        $this->currentStep = 1;
        $this->validateCurrentStep();
        $this->currentStep = 2;
        $this->validateCurrentStep();
        $this->currentStep = 3;
        $this->validateCurrentStep();
        $this->currentStep = 4;

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
                'status' => 'scheduled', // Force scheduled status
            ];

            if ($this->examId) {
                $exam = Exam::findOrFail($this->examId);
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

    public function publish()
    {
        $this->status = 'scheduled';
        $this->saveExam();
    }

    public function render()
    {
        $subjects = Subject::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();
        
        // Group questions by title for selection
        $questionGroups = Question::with(['subject'])
            ->when($this->searchQuery, function ($q) {
                $q->where('title', 'like', '%' . $this->searchQuery . '%');
            })
            ->when($this->filterSubject, function ($q) {
                $q->where('subject_id', $this->filterSubject);
            })
            ->when($this->filterType, function ($q) {
                $q->where('type', $this->filterType);
            })
            ->select('title', 'subject_id', \DB::raw('COUNT(*) as question_count'), \DB::raw('MIN(id) as first_id'))
            ->groupBy('title', 'subject_id')
            ->orderBy('title')
            ->get();

        return view('teacher.exam.form', [
            'subjects' => $subjects,
            'availableClasses' => $classrooms,
            'questionGroups' => $questionGroups,
        ])->layout('layouts.teacher')->title($this->examId ? 'Edit Ujian' : 'Buat Ujian Baru');
    }
}


