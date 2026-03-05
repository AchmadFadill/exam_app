<?php

namespace App\Livewire\Teacher;

use App\Models\Question;
use App\Models\QuestionGroup;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Exports\QuestionGroupExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class QuestionGroupDetail extends Component
{
    use WithFileUploads, WithPagination;

    public $groupId;
    public $title;
    public $subjectId = null;
    public $subjectName = null;
    public $selectedQuestions = [];
    public $showBulkDeleteModal = false;
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $selectedQuestion = null;
    public $optionCount = 5; // Number of options to show
    
    // Check Renaming State
    public $renamingTitle = false;
    public $newGroupTitle = '';
    
    public $questionImage;
    public $editingImagePath = null;
    public $optionImages = [];
    public $editingOptionImagePaths = [];
    public $editorInlineImage;
    
    public $questionForm = [
        'title' => '',
        'subject_id' => '',
        'type' => 'multiple_choice',
        'text' => '',
        'explanation' => '',
        'score' => 10,
        'options' => ['', '', '', '', ''], // Default 5 options
        'correct_option' => '',
    ];

    protected function rules()
    {
        $rules = [
            'questionForm.title' => 'required|string|max:255',
            'questionForm.subject_id' => 'required|exists:subjects,id',
            'questionForm.type' => 'required|in:multiple_choice,essay',
            'questionForm.text' => 'required|string|max:5000',
            'questionForm.explanation' => 'nullable|string|max:1000',
            'questionForm.score' => 'required',
            // Validation for renaming
            'newGroupTitle' => 'required|string|max:255',
        ];

        if ($this->questionImage) {
            $rules['questionImage'] = 'image|max:5120|mimes:jpg,jpeg,png,gif,svg';
        }
        $rules['optionImages.*'] = 'nullable|image|max:5120|mimes:jpg,jpeg,png,gif,svg';

        if ($this->questionForm['type'] === 'multiple_choice') {
            // Only validate the number of options currently shown
            for ($i = 0; $i < $this->optionCount; $i++) {
                $rules["questionForm.options.{$i}"] = 'required|string|max:500';
            }
            $rules['questionForm.correct_option'] = 'required|in:A,B,C,D,E';
        }

        return $rules;
    }

    private function validateQuestionForm(): void
    {
        $this->withValidator(function (Validator $validator): void {
            $validator->after(function (Validator $validator): void {
                $normalizedScore = $this->normalizedScoreValue($this->questionForm['score'] ?? null);

                if ($normalizedScore === null) {
                    $validator->errors()->add('questionForm.score', 'Bobot nilai harus berupa angka (integer/desimal) atau boolean.');
                    return;
                }

                if ($normalizedScore < 0 || $normalizedScore > 100) {
                    $validator->errors()->add('questionForm.score', 'Bobot nilai harus antara 0 sampai 100.');
                }
            });
        })->validate($this->rules());
    }

    public function mount($group)
    {
        $this->groupId = (int) $group;
        $groupModel = QuestionGroup::query()->with('subject')->findOrFail($this->groupId);

        $this->title = $groupModel->title;
        $this->subjectId = (int) $groupModel->subject_id;
        $this->subjectName = $groupModel->subject?->name;
        $this->newGroupTitle = $this->title;

        Gate::allowIf(function ($user) {
            if ($user->isAdmin()) {
                return true;
            }

            if (!$user->isTeacher()) {
                return false;
            }

            return QuestionGroup::query()
                ->whereKey($this->groupId)
                ->where('teacher_id', $user->teacher?->id)
                ->exists();
        });
    }

    public function startRenaming()
    {
        $this->renamingTitle = true;
        $this->newGroupTitle = $this->title;
    }

    public function cancelRenaming()
    {
        $this->renamingTitle = false;
        $this->newGroupTitle = $this->title;
    }

    public function updateGroupTitle()
    {
        $this->validate(['newGroupTitle' => 'required|string|max:255']);

        if ($this->newGroupTitle === $this->title) {
            $this->renamingTitle = false;
            return;
        }

        DB::transaction(function () {
            QuestionGroup::query()
                ->whereKey($this->groupId)
                ->update(['title' => $this->newGroupTitle]);

            // Keep legacy column in sync.
            $this->groupQuestionsQuery()->update(['title' => $this->newGroupTitle]);
        });

        // Redirect to new URL
        $route = Auth::user()->isAdmin() ? 'admin.questions.group' : 'teacher.questions.group';
        return redirect()->route($route, ['group' => $this->groupId])
            ->with('success', 'Nama kelompok soal berhasil diubah!');
    }

    public function getDisplayTitleProperty(): string
    {
        return $this->subjectName
            ? "{$this->title} - {$this->subjectName}"
            : $this->title;
    }

    public function toggleSelectAll()
    {
        $groupCount = $this->groupQuestionsQuery()->count();
        if (count($this->selectedQuestions) === $groupCount) {
            $this->selectedQuestions = [];
        } else {
            $this->selectedQuestions = $this->groupQuestionsQuery()->pluck('id')->toArray();
        }
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->questionForm['title'] = $this->title; // Pre-fill with current group title
        $this->questionForm['subject_id'] = $this->subjectId;
        
        $this->optionCount = 5;
        $this->showAddModal = true;
    }

    public function addOption()
    {
        if ($this->optionCount < 5) {
            $this->optionCount++;
            while (count($this->questionForm['options']) < $this->optionCount) {
                $this->questionForm['options'][] = '';
            }
        }
    }

    public function removeOption()
    {
        if ($this->optionCount > 2) {
            $this->optionCount--;
            if (isset($this->questionForm['options'][$this->optionCount])) {
                $this->questionForm['options'][$this->optionCount] = '';
            }
            if (isset($this->optionImages[$this->optionCount])) {
                $this->optionImages[$this->optionCount] = null;
            }
            $labels = ['A', 'B', 'C', 'D', 'E'];
            if ($this->questionForm['correct_option'] === $labels[$this->optionCount]) {
                $this->questionForm['correct_option'] = '';
            }
        }
    }

    public function openEditModal($questionId)
    {
        $this->selectedQuestion = $questionId;
        $question = $this->ownedQuestion($questionId);

        $this->questionForm = [
            'title' => $question->title ?? '',
            'subject_id' => $question->subject_id,
            'type' => $question->type,
            'text' => $question->text,
            'explanation' => $question->explanation ?? '',
            'score' => $question->score ?? 10,
            'options' => ['', '', '', '', ''],
            'correct_option' => '',
        ];

        $this->editingImagePath = $question->image_path;
        $this->editingOptionImagePaths = array_fill(0, 5, null);

        // Load options for multiple choice
        if ($question->type === 'multiple_choice') {
            $maxOptionIndex = -1;
            foreach ($question->options as $option) {
                $index = ord($option->label) - ord('A');
                if ($index >= 0 && $index < 5) {
                    $this->questionForm['options'][$index] = $option->text;
                    $this->editingOptionImagePaths[$index] = $option->image_path;
                    $maxOptionIndex = max($maxOptionIndex, $index);
                    if ($option->is_correct) {
                        $this->questionForm['correct_option'] = $option->label;
                    }
                }
            }

            $this->optionCount = max(5, $maxOptionIndex + 1);
        } else {
            $this->optionCount = 5;
            $this->editingOptionImagePaths = array_fill(0, 5, null);
        }

        $this->showEditModal = true;
    }

    public function saveQuestion()
    {
        $this->validateQuestionForm();
        $this->normalizeScoreInput();

        DB::transaction(function () {
            $imagePath = null;
            if ($this->questionImage) {
                $fileName = time() . '_' . $this->questionImage->getClientOriginalName();
                $imagePath = $this->questionImage->storeAs('questions', $fileName, 'public');
            }

            if ($this->showEditModal && $this->selectedQuestion) {
                // Update existing question
                $question = Question::findOrFail($this->selectedQuestion);
                if ($question->hasAttemptedExamUsage()) {
                    throw new \Exception('Soal sudah digunakan dalam ujian yang dikerjakan siswa, sehingga tidak dapat diubah.');
                }
                
                // Delete old image if new one uploaded
                if ($this->questionImage && $question->image_path) {
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($question->image_path)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($question->image_path);
                    }
                }

                $updateData = $this->getQuestionData();
                unset($updateData['teacher_id']); // Don't change teacher ownership

                if ($this->questionImage) {
                    $updateData['image_path'] = $imagePath;
                }

                $question->update($updateData);
            } else {
                // Create new question
                $createData = $this->getQuestionData();

                if ($this->questionImage) {
                    $createData['image_path'] = $imagePath;
                }

                $question = Question::create($createData);
            }

            // Delete old options and create new ones for multiple choice
            if ($this->questionForm['type'] === 'multiple_choice') {
                $question->options()->delete();
                $this->createOptions($question);
            } else {
                $question->options()->delete();
            }

            $message = $this->showAddModal ? 'Soal berhasil ditambahkan!' : 'Soal berhasil diperbarui!';
            $this->dispatch('notify', ['message' => $message]);
        });

        $this->showEditModal = false;
        $this->showAddModal = false;
        $this->resetForm();
    }

    public function saveAndAddAnother()
    {
        $this->validateQuestionForm();
        $this->normalizeScoreInput();

        DB::transaction(function () {
            $imagePath = null;
            if ($this->questionImage) {
                $fileName = time() . '_' . $this->questionImage->getClientOriginalName();
                $imagePath = $this->questionImage->storeAs('questions', $fileName, 'public');
            }

            $createData = $this->getQuestionData();

            if ($imagePath) {
                $createData['image_path'] = $imagePath;
            }

            $question = Question::create($createData);

            if ($this->questionForm['type'] === 'multiple_choice') {
                $this->createOptions($question);
            }

            $this->dispatch('notify', ['message' => 'Soal berhasil ditambahkan!']);
        });

        // Keep title and subject, clear others
        $keepTitle = $this->questionForm['title'];
        $keepSubject = $this->questionForm['subject_id'];
        $keepType = $this->questionForm['type'];
        
        $this->resetForm();
        
        $this->questionForm['title'] = $keepTitle;
        $this->questionForm['subject_id'] = $keepSubject;
        $this->questionForm['type'] = $keepType;
        $this->optionCount = 5;
    }

    private function getQuestionData()
    {
        $groupTeacherId = QuestionGroup::query()->whereKey($this->groupId)->value('teacher_id');
        $teacherId = $groupTeacherId;

        if (!$teacherId) {
            $user = Auth::user();
            $teacherId = $user->isTeacher()
                ? $user->teacher->id
                : \App\Models\Teacher::firstOrCreate(['user_id' => $user->id], ['nip' => null])->id;
        }

        return [
            'teacher_id' => $teacherId,
            'question_group_id' => $this->groupId,
            'title' => $this->questionForm['title'],
            'subject_id' => $this->questionForm['subject_id'],
            'type' => $this->questionForm['type'],
            'text' => $this->questionForm['text'],
            'explanation' => $this->questionForm['explanation'],
            'score' => $this->questionForm['score'],
        ];
    }

    private function normalizedScoreValue(mixed $value): ?float
    {
        if (is_bool($value)) {
            return $value ? 1.0 : 0.0;
        }

        if (is_int($value) || is_float($value)) {
            return is_finite((float) $value) ? (float) $value : null;
        }

        if (!is_string($value)) {
            return null;
        }

        $normalized = strtolower(trim($value));
        if (in_array($normalized, ['true', 'false', '1', '0'], true)) {
            return in_array($normalized, ['true', '1'], true) ? 1.0 : 0.0;
        }

        $numeric = str_replace(',', '.', trim($value));
        if (!is_numeric($numeric)) {
            return null;
        }

        $floatValue = (float) $numeric;

        return is_finite($floatValue) ? $floatValue : null;
    }

    private function normalizeScoreInput(): void
    {
        $normalizedScore = $this->normalizedScoreValue($this->questionForm['score'] ?? null);
        $this->questionForm['score'] = $normalizedScore !== null ? round($normalizedScore, 2) : 0;
    }

    public function removeImage()
    {
        $this->questionImage = null;
        
        if ($this->showEditModal && $this->selectedQuestion) {
            $question = Question::findOrFail($this->selectedQuestion);
            
            if ($question->image_path) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($question->image_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($question->image_path);
                }
                
                $question->update(['image_path' => null]);
                $this->editingImagePath = null;
                $this->dispatch('notify', ['message' => 'Gambar berhasil dihapus!']);
            }
        }
    }

    public function removeOptionImage(int $index): void
    {
        if ($index < 0 || $index > 4) {
            return;
        }

        $this->optionImages[$index] = null;

        if (!$this->showEditModal || !$this->selectedQuestion) {
            $this->editingOptionImagePaths[$index] = null;
            return;
        }

        $label = chr(65 + $index);
        $option = QuestionOption::where('question_id', $this->selectedQuestion)
            ->where('label', $label)
            ->first();

        if (!$option || !$option->image_path) {
            $this->editingOptionImagePaths[$index] = null;
            return;
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($option->image_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($option->image_path);
        }

        $option->update(['image_path' => null]);
        $this->editingOptionImagePaths[$index] = null;
        $this->dispatch('notify', ['message' => "Gambar opsi {$label} berhasil dihapus!"]);
    }

    public function storeInlineImage(): ?string
    {
        $this->validate([
            'editorInlineImage' => 'required|image|max:5120|mimes:jpg,jpeg,png,gif,svg,webp',
        ]);

        $fileName = time() . '_inline_' . $this->editorInlineImage->getClientOriginalName();
        $path = $this->editorInlineImage->storeAs('questions', $fileName, 'public');
        $this->editorInlineImage = null;

        return \Illuminate\Support\Facades\Storage::url($path);
    }

    private function createOptions($question)
    {
        $labels = ['A', 'B', 'C', 'D', 'E'];
        
        foreach ($labels as $index => $label) {
            if (!empty($this->questionForm['options'][$index])) {
                $imagePath = null;
                if (!empty($this->optionImages[$index])) {
                    $fileName = time() . "_opt_{$label}_" . $this->optionImages[$index]->getClientOriginalName();
                    $imagePath = $this->optionImages[$index]->storeAs('question-options', $fileName, 'public');
                }

                QuestionOption::create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'text' => $this->questionForm['options'][$index],
                    'image_path' => $imagePath,
                    'is_correct' => ($this->questionForm['correct_option'] === $label),
                ]);
            }
        }
    }

    public function closeModal()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->questionForm = [
            'title' => '',
            'subject_id' => '',
            'type' => 'multiple_choice',
            'text' => '',
            'explanation' => '',
            'options' => ['', '', '', '', ''],
            'correct_option' => '',
        ];
        $this->optionCount = 5;
        $this->questionImage = null;
        $this->editingImagePath = null;
        $this->optionImages = [];
        $this->editingOptionImagePaths = array_fill(0, 5, null);
        $this->editorInlineImage = null;
        $this->resetValidation();
    }

    public function openDeleteModal($questionId)
    {
        $this->ownedQuestion($questionId);
        $this->selectedQuestion = $questionId;
        $this->showDeleteModal = true;
    }

    public function deleteQuestion()
    {
        if ($this->selectedQuestion) {
            DB::transaction(function () {
                $question = Question::findOrFail($this->selectedQuestion);
                $this->ownedQuestion($question->id);
                $question->options()->delete();
                $question->delete();
            });

            $this->dispatch('notify', ['message' => 'Soal berhasil dihapus!']);
        }

        $this->showDeleteModal = false;
        $this->reset('selectedQuestion');
    }

    public function bulkDelete()
    {
        if (!empty($this->selectedQuestions)) {
            DB::transaction(function () {
                $questions = Question::with('options')
                    ->whereIn('id', $this->selectedQuestions)
                    ->when(\Illuminate\Support\Facades\Auth::user()->isTeacher(), function ($query) {
                        $query->where('teacher_id', \Illuminate\Support\Facades\Auth::user()->teacher->id);
                    })
                    ->get();
                
                foreach ($questions as $question) {
                    $question->options()->delete();
                    $question->delete();
                }
            });

            $count = count($this->selectedQuestions);
            $this->dispatch('notify', ['message' => "{$count} soal berhasil dihapus!"]);
            
            $this->selectedQuestions = [];
        }

        $this->showBulkDeleteModal = false;
    }

    public function distributeScores()
    {
        $questions = $this->groupQuestionsQuery()->orderBy('id')->get();
        $count = $questions->count();

        if ($count === 0) {
            return;
        }

        $totalScore = 100;
        $baseScore = intdiv($totalScore, $count);
        $remainder = $totalScore % $count;

        foreach ($questions as $index => $question) {
            $score = $baseScore + ($index < $remainder ? 1 : 0);
            $question->update(['score' => $score]);
        }

        $this->dispatch('notify', ['message' => 'Bobot nilai semua soal berhasil disesuaikan menjadi total 100!']);
    }

    public function exportGroupQuestions()
    {
        $teacherId = Auth::user()->isTeacher() ? Auth::user()->teacher?->id : null;

        return Excel::download(
            new QuestionGroupExport(groupId: $this->groupId, teacherId: $teacherId),
            'soal_' . \Illuminate\Support\Str::slug($this->title) . '_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function render()
    {
        $questions = $this->groupQuestionsQuery()
            ->with(['subject', 'options'])
            ->latest()
            ->paginate(10);
        
        $totalScore = $this->groupQuestionsQuery()->sum('score');
        
        $subjects = Subject::orderBy('name')->get();

        return view('livewire.teacher.question-group-detail', [
            'questions' => $questions,
            'subjects' => $subjects,
            'totalScore' => $totalScore,
        ])->layout(Auth::user()->isAdmin() ? 'layouts.admin' : 'layouts.teacher')->title('Detail Soal - ' . $this->displayTitle);
    }

    private function groupQuestionsQuery()
    {
        return Question::query()
            ->where('question_group_id', $this->groupId)
            ->when(\Illuminate\Support\Facades\Auth::user()->isTeacher(), function ($query) {
                $query->where('teacher_id', \Illuminate\Support\Facades\Auth::user()->teacher->id);
            });
    }

    private function ownedQuestion(int $questionId): Question
    {
        $question = Question::with('options')->findOrFail($questionId);

        Gate::allowIf(function ($user) use ($question) {
            if ($user->isAdmin()) {
                return true;
            }

            return $user->isTeacher() && (int) $question->teacher_id === (int) $user->teacher?->id;
        });

        return $question;
    }
}
