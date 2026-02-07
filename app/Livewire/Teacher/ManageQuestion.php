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
    public $showDeleteModal = false;
    public $showImportModal = false;
    public $showBulkDeleteModal = false;
    public $selectedQuestion = null;
    public $selectedQuestions = [];
    public $importFile;
    public $importTitle = ''; 
    
    // Filters
    public $search = '';
    public $filterSubject = '';
    public $filterType = '';
    
    protected $listeners = [
        'question-saved' => 'handleQuestionSaved'
    ];
    
    // ... [Validation rules removed as they are now in the child component] ...

    public function handleQuestionSaved($questionId)
    {
        $this->dispatch('notify', ['message' => 'Daftar soal diperbarui!']);
        // The render method will automatically refresh the list
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

    public function openAddModal()
    {
        $this->dispatch('openQuestionModal');
    }

    public function openEditModal($questionId)
    {
         $this->dispatch('openQuestionModal', ['questionId' => $questionId]);
    }

    public function openDeleteModal($questionId)
    {
        $this->selectedQuestion = $questionId;
        $question = Question::findOrFail($questionId);

        if (Auth::user()->isTeacher()) {
            $teacherId = $this->getTeacherId();
            if ($question->teacher_id !== $teacherId) {
                $this->dispatch('notify', ['message' => 'Anda tidak memiliki akses ke soal ini!', 'type' => 'error']);
                return;
            }
        }

        $this->showDeleteModal = true;
    }
    
    // ... [Old Save/Create/Update logic removed] ...


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

        // OPTIMIZED: Use pagination instead of loading all questions
        $allQuestions = $query->latest()->paginate(15);
        
        // Group questions by title
        $groupedQuestions = $allQuestions->groupBy(function($question) {
            return $question->title ?: 'Tanpa Kelompok';
        });

        $subjects = Subject::orderBy('name')->get();

        return view('teacher.manage-question', [
            'groupedQuestions' => $groupedQuestions,
            'subjects' => $subjects,
            'questions' => $allQuestions, // Pass paginated questions for pagination links
        ])->layout('layouts.teacher')->title('Bank Soal');
    }
}
