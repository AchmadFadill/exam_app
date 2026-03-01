<?php

namespace App\Livewire\Teacher;

use App\Imports\QuestionsImport;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Exports\QuestionGroupExport;
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
        'question-saved' => 'handleQuestionSaved',
        'open-import-modal' => 'openImportModal',
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
        $payload = [];
        if (!empty($this->filterSubject)) {
            $payload['subject_id'] = (int) $this->filterSubject;
        }

        $this->dispatch('openQuestionModal', $payload);
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
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:2048',
            'importTitle' => 'required|string|max:255',
        ], [
            'importFile.required' => 'File Excel wajib dipilih.',
            'importFile.mimes' => 'File harus berformat .xlsx, .xls, atau .csv.',
            'importTitle.required' => 'Judul kelompok soal wajib diisi.',
        ]);

        try {
            $import = new QuestionsImport($this->importTitle);
            Excel::import($import, $this->importFile->getRealPath());
            
            if ($import->importedCount > 0) {
                // Auto-distribute scores to 100 only when records exist
                Question::distributeScoresByTitle($this->importTitle);
            }
            
            $this->showImportModal = false;
            $this->reset(['importFile', 'importTitle']);

            $message = "Import selesai: {$import->importedCount} soal berhasil ditambahkan";
            if ($import->skippedCount > 0) {
                $message .= ", {$import->skippedCount} baris dilewati";
            }

            if (count($import->errors) > 0) {
                $message .= ", " . count($import->errors) . " baris gagal";
                $sampleErrors = implode(' | ', array_slice($import->errors, 0, 3));
                $this->dispatch('notify', [
                    'message' => $message . ". Error: {$sampleErrors}",
                    'type' => $import->importedCount > 0 ? 'warning' : 'error',
                ]);
            } else {
                $suffix = $import->importedCount > 0 ? ' dan bobot nilai disesuaikan!' : '.';
                $this->dispatch('notify', ['message' => $message . $suffix]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Gagal import: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function downloadTemplate()
    {
        // Streaming CSV is much faster than generating XLSX (PhpSpreadsheet), especially on Windows.
        $headers = [
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

        $rows = [
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

        return response()->streamDownload(function () use ($headers, $rows) {
            // UTF-8 BOM for Excel compatibility.
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'template_soal.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function exportGroup(string $title, ?int $subjectId = null)
    {
        $teacherId = Auth::user()->isTeacher() ? $this->getTeacherId() : null;

        return Excel::download(
            new QuestionGroupExport($title, $teacherId, $subjectId),
            'soal_' . \Illuminate\Support\Str::slug($title) . '_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function render()
    {
        $user = Auth::user();

        // 1. Base query for filtering
        $query = Question::query();

        // For teachers, only show their own questions
        if ($user->isTeacher()) {
            $teacherId = $this->getTeacherId();
            if ($teacherId) {
                $query->where('teacher_id', $teacherId);
            }
        }

        // Apply filters
        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        if ($this->filterSubject) {
            $query->where('subject_id', $this->filterSubject);
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        // 2. Paginate distinct groups by title + subject
        $paginatedTitles = $query->select('title', 'subject_id')
            ->groupBy('title', 'subject_id')
            ->orderByRaw('MAX(created_at) DESC')
            ->paginate(9); // 9 groups per page

        // 3. Fetch ALL questions for the exact groups shown on this page
        $groups = $paginatedTitles->getCollection()
            ->map(fn ($row) => [
                'title' => (string) $row->title,
                'subject_id' => (int) $row->subject_id,
            ])
            ->all();

        $questionsInPage = collect();
        if (!empty($groups)) {
            $questionsInPage = Question::with(['subject', 'options'])
                ->where(function ($groupQuery) use ($groups) {
                    foreach ($groups as $group) {
                        $groupQuery->orWhere(function ($rowQuery) use ($group) {
                            $rowQuery
                                ->where('title', $group['title'])
                                ->where('subject_id', $group['subject_id']);
                        });
                    }
                })
                ->when($user->isTeacher(), function($q) {
                    $teacherId = $this->getTeacherId();
                     if ($teacherId) {
                        $q->where('teacher_id', $teacherId);
                    }
                })
                ->latest()
                ->get();
        }

        // 4. Group by title + subject and prepare safe payload for Blade
        $groupedQuestions = $questionsInPage
            ->groupBy(function ($question) {
                return $this->buildGroupKey((string) ($question->title ?: 'Tanpa Kelompok'), (int) $question->subject_id);
            })
            ->map(function ($questions) {
                $first = $questions->first();

                return [
                    'title' => (string) ($first->title ?: 'Tanpa Kelompok'),
                    'subject_id' => (int) $first->subject_id,
                    'subject_name' => $first->subject?->name ?? '-',
                    'questions' => $questions->values(),
                    'latest_created_at' => $questions->max('created_at'),
                ];
            })
            ->sortByDesc('latest_created_at')
            ->values();

        $subjects = $user->isTeacher()
            ? Auth::user()->teacher?->subjects()->orderBy('name')->get() ?? collect()
            : Subject::orderBy('name')->get();

        return view('teacher.manage-question', [
            'groupedQuestions' => $groupedQuestions,
            'subjects' => $subjects,
            'paginatedTitles' => $paginatedTitles, // Pass this for pagination links
        ])->layout($user->isAdmin() ? 'layouts.admin' : 'layouts.teacher')->title('Bank Soal');
    }

    private function buildGroupKey(string $title, int $subjectId): string
    {
        return $title . '||' . $subjectId;
    }
}
