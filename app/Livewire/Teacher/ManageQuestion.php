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
    public $selectedQuestion = null;
    public $importFile;

    // Form Data
    public $questionForm = [
        'subject_id' => '',
        'type' => 'multiple_choice',
        'text' => '',
        'explanation' => '',
        'options' => ['', '', '', '', ''], // 5 options for A-E
        'correct_option' => '',
    ];

    // Filters
    public $search = '';
    public $filterSubject = '';
    public $filterType = '';

    protected function rules()
    {
        $rules = [
            'questionForm.subject_id' => 'required|exists:subjects,id',
            'questionForm.type' => 'required|in:multiple_choice,essay',
            'questionForm.text' => 'required|string|max:5000',
            'questionForm.explanation' => 'nullable|string|max:1000',
        ];

        // Add validation for multiple choice options
        if ($this->questionForm['type'] === 'multiple_choice') {
            $rules['questionForm.options.0'] = 'required|string|max:500';
            $rules['questionForm.options.1'] = 'required|string|max:500';
            $rules['questionForm.options.2'] = 'required|string|max:500';
            $rules['questionForm.options.3'] = 'required|string|max:500';
            $rules['questionForm.options.4'] = 'required|string|max:500';
            $rules['questionForm.correct_option'] = 'required|in:A,B,C,D,E';
        }

        return $rules;
    }

    protected $messages = [
        'questionForm.subject_id.required' => 'Mata pelajaran wajib dipilih.',
        'questionForm.text.required' => 'Pertanyaan wajib diisi.',
        'questionForm.text.max' => 'Pertanyaan maksimal 5000 karakter.',
        'questionForm.options.*.required' => 'Semua opsi jawaban wajib diisi.',
        'questionForm.correct_option.required' => 'Jawaban benar wajib dipilih.',
    ];

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

    public function openEditModal($questionId)
    {
        $this->selectedQuestion = $questionId;
        $question = Question::with('options')->findOrFail($questionId);

        // Check if question belongs to current teacher
        if ($question->teacher_id !== Auth::id()) {
            $this->dispatch('notify', ['message' => 'Anda tidak memiliki akses ke soal ini!', 'type' => 'error']);
            return;
        }

        $this->questionForm = [
            'subject_id' => $question->subject_id,
            'type' => $question->type,
            'text' => $question->text,
            'explanation' => $question->explanation ?? '',
            'options' => ['', '', '', '', ''],
            'correct_option' => '',
        ];

        // Load options for multiple choice
        if ($question->type === 'multiple_choice') {
            foreach ($question->options as $option) {
                $index = ord($option->label) - ord('A'); // Convert A,B,C,D,E to 0,1,2,3,4
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

    public function openDeleteModal($questionId)
    {
        $this->selectedQuestion = $questionId;
        $question = Question::findOrFail($questionId);

        // Check if question belongs to current teacher
        if ($question->teacher_id !== Auth::id()) {
            $this->dispatch('notify', ['message' => 'Anda tidak memiliki akses ke soal ini!', 'type' => 'error']);
            return;
        }

        $this->showDeleteModal = true;
    }

    public function saveQuestion()
    {
        $this->validate();

        DB::transaction(function () {
            if ($this->showEditModal && $this->selectedQuestion) {
                // Update existing question
                $question = Question::findOrFail($this->selectedQuestion);
                
                $question->update([
                    'subject_id' => $this->questionForm['subject_id'],
                    'type' => $this->questionForm['type'],
                    'text' => $this->questionForm['text'],
                    'explanation' => $this->questionForm['explanation'],
                ]);

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
                $question = Question::create([
                    'teacher_id' => Auth::id(),
                    'subject_id' => $this->questionForm['subject_id'],
                    'type' => $this->questionForm['type'],
                    'text' => $this->questionForm['text'],
                    'explanation' => $this->questionForm['explanation'],
                ]);

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
            'subject_id' => '',
            'type' => 'multiple_choice',
            'text' => '',
            'explanation' => '',
            'options' => ['', '', '', '', ''],
            'correct_option' => '',
        ];
        $this->selectedQuestion = null;
        $this->importFile = null;
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
        ]);

        try {
            Excel::import(new QuestionsImport, $this->importFile->getRealPath());
            
            $this->showImportModal = false;
            $this->reset('importFile');
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
        $query = Question::with(['subject', 'options'])
            ->where('teacher_id', Auth::id());

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

        $questions = $query->latest()->paginate(10);
        $subjects = Subject::orderBy('name')->get();

        return view('teacher.manage-question', [
            'questions' => $questions,
            'subjects' => $subjects,
        ])->layout('layouts.teacher')->title('Bank Soal');
    }
}
