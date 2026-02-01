<?php

namespace App\Livewire\Teacher;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class QuestionGroupDetail extends Component
{
    use WithFileUploads;

    public $title;
    public $selectedQuestions = [];
    public $showBulkDeleteModal = false;
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $selectedQuestion = null;
    public $optionCount = 4; // Number of options to show
    
    public $questionImage;
    public $editingImagePath = null;
    
    public $questionForm = [
        'title' => '',
        'subject_id' => '',
        'type' => 'multiple_choice',
        'text' => '',
        'explanation' => '',
        'score' => 10,
        'options' => ['', '', '', ''], // Default 4 options
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
            'questionForm.score' => 'required|integer|min:1|max:100',
        ];

        if ($this->questionImage) {
            $rules['questionImage'] = 'image|max:5120|mimes:jpg,jpeg,png,gif,svg';
        }

        if ($this->questionForm['type'] === 'multiple_choice') {
            // Only validate the number of options currently shown
            for ($i = 0; $i < $this->optionCount; $i++) {
                $rules["questionForm.options.{$i}"] = 'required|string|max:500';
            }
            $rules['questionForm.correct_option'] = 'required|in:A,B,C,D,E';
        }

        return $rules;
    }

    public function mount($title)
    {
        $this->title = urldecode($title);
    }

    public function toggleSelectAll()
    {
        if (count($this->selectedQuestions) === Question::where('title', $this->title)->count()) {
            $this->selectedQuestions = [];
        } else {
            $this->selectedQuestions = Question::where('title', $this->title)->pluck('id')->toArray();
        }
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->questionForm['title'] = $this->title; // Pre-fill with current group title
        
        // Auto-fill subject from first question in this group
        $firstQuestion = Question::where('title', $this->title)->first();
        if ($firstQuestion) {
            $this->questionForm['subject_id'] = $firstQuestion->subject_id;
        }
        
        $this->optionCount = 4;
        $this->showAddModal = true;
    }

    public function addOption()
    {
        if ($this->optionCount < 4) {
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
            $labels = ['A', 'B', 'C', 'D', 'E'];
            if ($this->questionForm['correct_option'] === $labels[$this->optionCount]) {
                $this->questionForm['correct_option'] = '';
            }
        }
    }

    public function openEditModal($questionId)
    {
        $this->selectedQuestion = $questionId;
        $question = Question::with('options')->findOrFail($questionId);

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

        // Load options for multiple choice
        if ($question->type === 'multiple_choice') {
            foreach ($question->options as $option) {
                $index = ord($option->label) - ord('A');
                if ($index >= 0 && $index < 5) {
                    $this->questionForm['options'][$index] = $option->text;
                    if ($option->is_correct) {
                        $this->questionForm['correct_option'] = $option->label;
                    }
                }
            }
        }

        $this->showEditModal = true;
    }

    public function saveQuestion()
    {
        $this->validate();

        DB::transaction(function () {
            $imagePath = null;
            if ($this->questionImage) {
                $fileName = time() . '_' . $this->questionImage->getClientOriginalName();
                $imagePath = $this->questionImage->storeAs('questions', $fileName, 'public');
            }

            if ($this->showEditModal && $this->selectedQuestion) {
                // Update existing question
                $question = Question::findOrFail($this->selectedQuestion);
                
                // Delete old image if new one uploaded
                if ($this->questionImage && $question->image_path) {
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($question->image_path)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($question->image_path);
                    }
                }

                $updateData = [
                    'title' => $this->questionForm['title'],
                    'subject_id' => $this->questionForm['subject_id'],
                    'type' => $this->questionForm['type'],
                    'text' => $this->questionForm['text'],
                    'explanation' => $this->questionForm['explanation'],
                    'score' => $this->questionForm['score'],
                ];

                if ($this->questionImage) {
                    $updateData['image_path'] = $imagePath;
                }

                $question->update($updateData);
            } else {
                // Create new question
                $user = \Illuminate\Support\Facades\Auth::user();
                $teacherId = $user->isTeacher() 
                    ? $user->teacher->id 
                    : \App\Models\Teacher::first()->id;

                $createData = [
                    'teacher_id' => $teacherId,
                    'title' => $this->questionForm['title'],
                    'subject_id' => $this->questionForm['subject_id'],
                    'type' => $this->questionForm['type'],
                    'text' => $this->questionForm['text'],
                    'explanation' => $this->questionForm['explanation'],
                    'score' => $this->questionForm['score'],
                ];

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
        $this->validate();

        DB::transaction(function () {
            $user = \Illuminate\Support\Facades\Auth::user();
            $teacherId = $user->isTeacher() 
                ? $user->teacher->id 
                : \App\Models\Teacher::first()->id;

            $imagePath = null;
            if ($this->questionImage) {
                $fileName = time() . '_' . $this->questionImage->getClientOriginalName();
                $imagePath = $this->questionImage->storeAs('questions', $fileName, 'public');
            }

            $createData = [
                'teacher_id' => $teacherId,
                'title' => $this->questionForm['title'],
                'subject_id' => $this->questionForm['subject_id'],
                'type' => $this->questionForm['type'],
                'text' => $this->questionForm['text'],
                'explanation' => $this->questionForm['explanation'],
                'score' => $this->questionForm['score'],
            ];

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
        $this->optionCount = 4;
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

    private function createOptions($question)
    {
        $labels = ['A', 'B', 'C', 'D', 'E'];
        
        foreach ($labels as $index => $label) {
            if (!empty($this->questionForm['options'][$index])) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'text' => $this->questionForm['options'][$index],
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
        $this->questionImage = null;
        $this->editingImagePath = null;
        $this->resetValidation();
    }

    public function openDeleteModal($questionId)
    {
        $this->selectedQuestion = $questionId;
        $this->showDeleteModal = true;
    }

    public function deleteQuestion()
    {
        if ($this->selectedQuestion) {
            DB::transaction(function () {
                $question = Question::findOrFail($this->selectedQuestion);
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
                $questions = Question::with('options')->whereIn('id', $this->selectedQuestions)->get();
                
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

    public function render()
    {
        $questions = Question::with(['subject', 'options'])
            ->where('title', $this->title)
            ->latest()
            ->get();
        
        $subjects = Subject::orderBy('name')->get();

        return view('livewire.teacher.question-group-detail', [
            'questions' => $questions,
            'subjects' => $subjects,
        ])->layout('layouts.teacher')->title('Detail Soal - ' . $this->title);
    }
}
