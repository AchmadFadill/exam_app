<?php

namespace App\Livewire\Teacher;

use App\Imports\QuestionsImport;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ManageQuestion extends Component
{
    use WithPagination, WithFileUploads;

    // Modal States
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showImportModal = false;
    public $showBulkDeleteModal = false;
    public $selectedQuestion = null;
    public $selectedQuestions = [];
    public $importFile;
    public $importTitle = ''; // Title for imported questions group
    public $formKey = 0; // Used to force form re-render
    public $optionCount = 4; // Number of options to show (min 2, max 5)
    
    // Image Upload
    public $questionImage;
    public $editingImagePath = null;

    // Form Data
    public $questionForm = [
        'title' => '',
        'subject_id' => '',
        'type' => 'multiple_choice',
        'text' => '',
        'explanation' => '',
        'score' => 10,
        'options' => ['', '', '', ''], // Default 4 options A-D
        'correct_option' => '',
    ];

    // Filters
    public $search = '';
    public $filterSubject = '';
    public $filterType = '';

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

        // Add validation for multiple choice options - only validate shown options
        if ($this->questionForm['type'] === 'multiple_choice') {
            for ($i = 0; $i < $this->optionCount; $i++) {
                $rules["questionForm.options.{$i}"] = 'required|string|max:500';
            }
            $rules['questionForm.correct_option'] = 'required|in:A,B,C,D,E';
        }

        return $rules;
    }

    protected $messages = [
        'questionForm.title.required' => 'Judul kelompok soal wajib diisi.',
        'questionForm.subject_id.required' => 'Mata pelajaran wajib dipilih.',
        'questionForm.text.required' => 'Pertanyaan wajib diisi.',
        'questionForm.text.max' => 'Pertanyaan maksimal 5000 karakter.',
        'questionForm.options.*.required' => 'Semua opsi jawaban wajib diisi.',
        'questionForm.correct_option.required' => 'Jawaban benar wajib dipilih.',
        'questionImage.image' => 'File harus berupa gambar.',
        'questionImage.max' => 'Ukuran gambar maksimal 5MB.',
        'questionImage.mimes' => 'Format gambar harus JPG, JPEG, PNG, GIF, atau SVG.',
    ];

    /**
     * Get the teacher ID for the current user
     * For admins, return null (we'll handle this differently)
     * For teachers, return their teacher record ID
     */
    private function getTeacherId()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            // For admin, find the first teacher or return null
            $teacher = \App\Models\Teacher::first();
            return $teacher ? $teacher->id : null;
        }
        
        if ($user->isTeacher()) {
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
            return $teacher ? $teacher->id : null;
        }
        
        return null;
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->optionCount = 4; // Reset to 4 options
        $this->showAddModal = true;
    }

    public function addOption()
    {
        if ($this->optionCount < 5) {
            $this->optionCount++;
            // Ensure options array has enough elements
            while (count($this->questionForm['options']) < $this->optionCount) {
                $this->questionForm['options'][] = '';
            }
        }
    }

    public function removeOption()
    {
        if ($this->optionCount > 2) {
            $this->optionCount--;
            // Clear the last option
            if (isset($this->questionForm['options'][$this->optionCount])) {
                $this->questionForm['options'][$this->optionCount] = '';
            }
            // If correct option is the removed one, clear it
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

        // Check if question belongs to current teacher (admins can edit any question)
        if (Auth::user()->isTeacher()) {
            $teacherId = $this->getTeacherId();
            if ($question->teacher_id !== $teacherId) {
                $this->dispatch('notify', ['message' => 'Anda tidak memiliki akses ke soal ini!', 'type' => 'error']);
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

        // Load options for multiple choice
        if ($question->type === 'multiple_choice') {
            $maxIndex = 0;
            foreach ($question->options as $option) {
                $index = ord($option->label) - ord('A');
                if ($index >= 0 && $index < 5) {
                    // Ensure options array is large enough
                    while (count($this->questionForm['options']) <= $index) {
                        $this->questionForm['options'][] = '';
                    }
                    $this->questionForm['options'][$index] = $option->text;
                    if ($option->is_correct) {
                        $this->questionForm['correct_option'] = $option->label;
                    }
                    $maxIndex = max($maxIndex, $index);
                }
            }
            $this->optionCount = $maxIndex + 1; // Set option count based on existing options
        } else {
            $this->optionCount = 4;
        }

        $this->showEditModal = true;
    }

    public function openDeleteModal($questionId)
    {
        $this->selectedQuestion = $questionId;
        $question = Question::findOrFail($questionId);

        // Check if question belongs to current teacher (admins can delete any question)
        if (Auth::user()->isTeacher()) {
            $teacherId = $this->getTeacherId();
            if ($question->teacher_id !== $teacherId) {
                $this->dispatch('notify', ['message' => 'Anda tidak memiliki akses ke soal ini!', 'type' => 'error']);
                return;
            }
        }

        $this->showDeleteModal = true;
    }

    public function saveQuestion()
    {
        try {
            $this->validate();

            DB::transaction(function () {
                $imagePath = null;
                
                // Handle Image Upload
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

                    // Delete old options and create new ones for multiple choice
                    if ($this->questionForm['type'] === 'multiple_choice') {
                        $question->options()->delete();
                        $this->createOptions($question);
                    } else {
                        // Delete options if changed to essay
                        $question->options()->delete();
                    }

                    $message = 'Soal berhasil diperbarui!';
                } else {
                    // Create new question
                    $teacherId = $this->getTeacherId();
                    
                    if (!$teacherId) {
                        throw new \Exception('Tidak dapat menemukan teacher ID. Pastikan ada data guru di sistem.');
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

                    if ($this->questionImage) {
                        $createData['image_path'] = $imagePath;
                    }

                    $question = Question::create($createData);

                    // Create options for multiple choice
                    if ($this->questionForm['type'] === 'multiple_choice') {
                        $this->createOptions($question);
                    }

                    $message = 'Soal berhasil ditambahkan!';
                }

                $this->dispatch('notify', ['message' => $message]);
            });

            $this->showAddModal = false;
            $this->showEditModal = false;
            $this->resetForm();
            // Component will auto-refresh
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors will be shown automatically by Livewire
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Error: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function saveAndAddAnother()
    {
        $this->validate();

        DB::transaction(function () {
            $teacherId = $this->getTeacherId();
            
            if (!$teacherId) {
                throw new \Exception('Tidak dapat menemukan teacher ID. Pastikan ada data guru di sistem.');
            }
            
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

            // Create options for multiple choice
            if ($this->questionForm['type'] === 'multiple_choice') {
                $this->createOptions($question);
            }

            $this->dispatch('notify', ['message' => 'Soal berhasil ditambahkan! Silakan tambah soal berikutnya.']);
        });

        // Reset form but keep modal open - keep title, subject and type for same group
        $keepTitle = $this->questionForm['title'];
        $keepSubject = $this->questionForm['subject_id'];
        $keepType = $this->questionForm['type'];
        
        $this->questionForm = [
            'title' => $keepTitle, // Keep same title to add more questions to this group
            'subject_id' => $keepSubject,
            'type' => $keepType,
            'text' => '',
            'explanation' => '',
            'options' => ['', '', '', '', ''],
            'correct_option' => '',
        ];
        $this->questionImage = null;
        $this->editingImagePath = null;
        $this->resetValidation();
        $this->formKey++; // Force form to re-render with cleared text field
        // Component will auto-refresh to show new question
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
            QuestionOption::create([
                'question_id' => $question->id,
                'label' => $label,
                'text' => $this->questionForm['options'][$index],
                'is_correct' => $label === $this->questionForm['correct_option'],
            ]);
        }
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

    public function closeModal()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showImportModal = false;
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
        $this->selectedQuestion = null;
        $this->importFile = null;
        $this->questionImage = null;
        $this->editingImagePath = null;
        $this->resetValidation();
    }

    // Import Methods
    public function openImportModal()
    {
        $this->showImportModal = true;
    }

    public function importQuestions()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls|max:2048',
            'importTitle' => 'required|string|max:255',
        ], [
            'importFile.required' => 'File Excel wajib dipilih.',
            'importFile.mimes' => 'File harus berformat Excel (.xlsx atau .xls).',
            'importTitle.required' => 'Judul kelompok soal wajib diisi.',
        ]);

        try {
            Excel::import(new QuestionsImport($this->importTitle), $this->importFile->getRealPath());
            
            $this->showImportModal = false;
            $this->reset(['importFile', 'importTitle']);
            $this->dispatch('notify', ['message' => 'Soal berhasil diimport!']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Gagal import: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function array(): array
            {
                return [
                    [
                        'Matematika',
                        'multiple_choice',
                        'Berapakah hasil dari 2 + 2?',
                        '2',
                        '3',
                        '4',
                        '5',
                        '6',
                        'C',
                        'Hasil penjumlahan 2 + 2 adalah 4',
                    ],
                    [
                        'Bahasa Indonesia',
                        'essay',
                        'Jelaskan pengertian pantun!',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        'Pantun adalah bentuk puisi lama yang terdiri dari 4 baris',
                    ],
                ];
            }

            public function headings(): array
            {
                return [
                    'Mata Pelajaran',
                    'Tipe',
                    'Pertanyaan',
                    'Opsi A',
                    'Opsi B',
                    'Opsi C',
                    'Opsi D',
                    'Opsi E',
                    'Jawaban Benar',
                    'Pembahasan',
                ];
            }
        }, 'template_soal.xlsx');
    }

    public function render()
    {
        $query = Question::with(['subject', 'options']);
        
        // For teachers, only show their own questions
        // For admins, show all questions
        if (Auth::user()->isTeacher()) {
            $teacherId = $this->getTeacherId();
            if ($teacherId) {
                $query->where('teacher_id', $teacherId);
            }
        }

        // Apply search
        if ($this->search) {
            $query->where('text', 'like', '%' . $this->search . '%');
        }

        // Apply subject filter
        if ($this->filterSubject) {
            $query->where('subject_id', $this->filterSubject);
        }

        // Apply type filter
        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        // Get all questions and group them
        $allQuestions = $query->latest()->get();
        
        // Group questions by title
        $groupedQuestions = $allQuestions->groupBy(function($question) {
            return $question->title ?: 'Tanpa Kelompok';
        });

        $subjects = Subject::orderBy('name')->get();

        return view('teacher.manage-question', [
            'groupedQuestions' => $groupedQuestions,
            'subjects' => $subjects,
        ])->layout('layouts.teacher')->title('Bank Soal');
    }
}
