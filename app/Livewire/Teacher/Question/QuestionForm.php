<?php

namespace App\Livewire\Teacher\Question;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class QuestionForm extends Component
{
    use WithFileUploads;

    public $isOpen = false;
    public $isEdit = false;
    public $questionId = null;

    // Form Data
    public $questionForm = [
        'title' => '',
        'subject_id' => '',
        'type' => 'multiple_choice',
        'text' => '',
        'explanation' => '',
        'score' => 10,
        'options' => ['', '', '', '', ''], // Default 5 options A-E
        'correct_option' => '',
    ];

    public $optionCount = 5;
    public $subjects = [];
    
    // Image Upload
    public $questionImage;
    public $editingImagePath = null;
    
    // Configuration
    public $keepOpen = false; // For "Save & Add Another"
    public $defaultSubjectId = null; // Pre-select subject
    
    protected $listeners = ['openQuestionModal', 'closeQuestionModal'];

    public function mount()
    {
        $user = Auth::user();

        if ($user->isTeacher() && $user->teacher) {
            $this->subjects = $user->teacher->subjects()->orderBy('name')->get();
        } else {
            $this->subjects = \App\Models\Subject::orderBy('name')->get();
        }

        $this->defaultSubjectId = $this->subjects->first()?->id;
    }

    public function openQuestionModal($params = [])
    {
        $this->resetForm();
        $this->isOpen = true;
        
        if (isset($params['questionId'])) {
            $this->isEdit = true;
            $this->loadQuestion($params['questionId']);
        } else {
            $this->isEdit = false;
            // Pre-fill defaults if provided
            if (isset($params['subject_id'])) {
                $this->questionForm['subject_id'] = $params['subject_id'];
            } elseif ($this->defaultSubjectId) {
                // Default to teacher-assigned subject.
                $this->questionForm['subject_id'] = $this->defaultSubjectId;
            }
            if (isset($params['title'])) {
                $this->questionForm['title'] = $params['title'];
            }
        }
    }

    public function closeQuestionModal()
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    public function openImportFromForm()
    {
        $this->closeQuestionModal();
        $this->dispatch('open-import-modal');
    }

    public function loadQuestion($id)
    {
        $this->questionId = $id;
        $question = Question::with('options')->findOrFail($id);

        if (Auth::user()->isTeacher()) {
            $teacherId = $this->getTeacherId();
            if ($question->teacher_id !== $teacherId) {
                $this->dispatch('notify', ['message' => 'Anda tidak memiliki akses ke soal ini!', 'type' => 'error']);
                $this->closeQuestionModal();
                return;
            }
        }

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

        if ($question->type === 'multiple_choice') {
            $maxIndex = 0;
            $this->questionForm['options'] = []; // Reset options
            
            foreach ($question->options as $option) {
                $index = ord($option->label) - ord('A');
                // Ensure array has accumulated/sparse index handling if needed, 
                // but simpler to just fill up to the index
                while (count($this->questionForm['options']) <= $index) {
                    $this->questionForm['options'][] = '';
                }
                
                $this->questionForm['options'][$index] = $option->text;
                if ($option->is_correct) {
                    $this->questionForm['correct_option'] = $option->label;
                }
                $maxIndex = max($maxIndex, $index);
            }
            
            // Fill gaps if any
            $this->optionCount = max(count($this->questionForm['options']), 5);
             while (count($this->questionForm['options']) < $this->optionCount) {
                $this->questionForm['options'][] = '';
            }
        } else {
            $this->optionCount = 5;
             $this->questionForm['options'] = ['', '', '', '', ''];
        }
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
            // Clear the removed option
             if (isset($this->questionForm['options'][$this->optionCount])) {
                $this->questionForm['options'][$this->optionCount] = '';
            }
            
            // Reset correct option if it was the removed one
            $labels = ['A', 'B', 'C', 'D', 'E'];
            if ($this->questionForm['correct_option'] === $labels[$this->optionCount]) {
                $this->questionForm['correct_option'] = '';
            }
        }
    }
    
    public function removeImage()
    {
        $this->questionImage = null;
        
        if ($this->isEdit && $this->questionId) {
            $question = Question::findOrFail($this->questionId);
            if ($question->image_path) {
                if (Storage::disk('public')->exists($question->image_path)) {
                    Storage::disk('public')->delete($question->image_path);
                }
                $question->update(['image_path' => null]);
                $this->editingImagePath = null;
                 $this->dispatch('notify', ['message' => 'Gambar berhasil dihapus!']);
            }
        }
    }

    public function save($addAnother = false)
    {
        $this->validate($this->getRules(), $this->getMessages());

        try {
            $questionId = DB::transaction(function () {
                $imagePath = null;
                
                if ($this->questionImage) {
                    $fileName = time() . '_' . $this->questionImage->getClientOriginalName();
                    $imagePath = $this->questionImage->storeAs('questions', $fileName, 'public');
                }

                $data = [
                    'title' => $this->questionForm['title'],
                    'subject_id' => $this->questionForm['subject_id'],
                    'type' => $this->questionForm['type'],
                    'text' => $this->questionForm['text'],
                    'explanation' => $this->questionForm['explanation'],
                    'score' => $this->questionForm['score'],
                ];
                
                if ($this->questionImage) {
                    $data['image_path'] = $imagePath;
                }

                if ($this->isEdit && $this->questionId) {
                    // Update
                    $question = Question::findOrFail($this->questionId);
                    
                    // Delete old image if new one uploaded
                    if ($this->questionImage && $question->image_path) {
                        if (Storage::disk('public')->exists($question->image_path)) {
                            Storage::disk('public')->delete($question->image_path);
                        }
                    }
                    
                    $question->update($data);
                    
                    // Keep option IDs stable when editing to avoid breaking historical selected_option_id references.
                    if ($this->questionForm['type'] === 'multiple_choice') {
                        $this->syncOptions($question);
                    } else {
                        $question->options()->delete();
                    }
                    
                    $this->dispatch('notify', ['message' => 'Soal berhasil diperbarui!']);
                    return $question->id;
                    
                } else {
                    // Create
                    $teacherId = $this->getTeacherId();
                    if (!$teacherId) throw new \Exception('Teacher ID not found.');
                    
                    $data['teacher_id'] = $teacherId;
                    $question = Question::create($data);
                    
                    if ($this->questionForm['type'] === 'multiple_choice') {
                        $this->createOptions($question);
                    }
                    
                    $this->dispatch('notify', ['message' => 'Soal berhasil dibuat!']);
                    return $question->id;
                }
            });

            // Emit event to parent components
            $this->dispatch('question-saved', questionId: $questionId);

            if ($addAnother) {
                 // Keep title, subject, type
                 $keep = [
                    'title' => $this->questionForm['title'],
                    'subject_id' => $this->questionForm['subject_id'],
                    'type' => $this->questionForm['type'],
                 ];
                 $this->resetForm();
                 $this->questionForm = array_merge($this->questionForm, $keep);
                 // Don't close modal
            } else {
                $this->closeQuestionModal();
            }

        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Error: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    private function createOptions($question)
    {
        $labels = ['A', 'B', 'C', 'D', 'E'];
        foreach ($labels as $index => $label) {
            if ($index < $this->optionCount) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'text' => $this->questionForm['options'][$index],
                    'is_correct' => $label === $this->questionForm['correct_option'],
                ]);
            }
        }
    }

    private function syncOptions($question): void
    {
        $labels = ['A', 'B', 'C', 'D', 'E'];
        $existing = $question->options()->get()->keyBy('label');
        $keepLabels = [];

        foreach ($labels as $index => $label) {
            if ($index >= $this->optionCount) {
                continue;
            }

            $payload = [
                'text' => $this->questionForm['options'][$index],
                'is_correct' => $label === $this->questionForm['correct_option'],
            ];

            if ($existing->has($label)) {
                $existing[$label]->update($payload);
            } else {
                QuestionOption::create(array_merge($payload, [
                    'question_id' => $question->id,
                    'label' => $label,
                ]));
            }

            $keepLabels[] = $label;
        }

        $question->options()
            ->whereNotIn('label', $keepLabels)
            ->delete();
    }

    private function getTeacherId()
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return \App\Models\Teacher::first()?->id;
        }
        if ($user->isTeacher()) {
            return \App\Models\Teacher::where('user_id', $user->id)->first()?->id;
        }
        return null;
    }

    private function resetForm()
    {
        $this->questionForm = [
            'title' => '',
            'subject_id' => '',
            'type' => 'multiple_choice',
            'text' => '',
            'explanation' => '',
            'score' => 10,
            'options' => ['', '', '', '', ''],
            'correct_option' => '',
        ];
        $this->optionCount = 5;
        $this->questionImage = null;
        $this->editingImagePath = null;
        $this->questionId = null;
        $this->isEdit = false;
        $this->resetValidation();
    }

    public function getRules()
    {
        $rules = [
            'questionForm.title' => 'required|string|max:255',
            'questionForm.subject_id' => 'required|exists:subjects,id',
            'questionForm.type' => 'required|in:multiple_choice,essay',
            'questionForm.text' => 'required|string',
            'questionForm.explanation' => 'nullable|string|max:1000',
            'questionForm.score' => 'required|integer|min:1|max:100',
        ];

        if ($this->questionImage) {
            $rules['questionImage'] = 'image|max:5120|mimes:jpg,jpeg,png,gif,svg';
        }

        if ($this->questionForm['type'] === 'multiple_choice') {
            for ($i = 0; $i < $this->optionCount; $i++) {
                $rules["questionForm.options.{$i}"] = 'required|string';
            }
            $rules['questionForm.correct_option'] = 'required|in:A,B,C,D,E';
        }

        return $rules;
    }
    
    public function getMessages() {
         return [
            'questionForm.title.required' => 'Judul kelompok soal wajib diisi.',
            'questionForm.subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'questionForm.text.required' => 'Pertanyaan wajib diisi.',
            'questionForm.options.*.required' => 'Semua opsi jawaban wajib diisi.',
            'questionForm.correct_option.required' => 'Jawaban benar wajib dipilih.',
         ];
    }
    
    public function render()
    {
        return view('livewire.teacher.question.question-form');
    }
}
