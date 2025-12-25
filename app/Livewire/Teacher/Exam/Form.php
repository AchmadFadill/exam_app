<?php

namespace App\Livewire\Teacher\Exam;

use Livewire\Component;

class Form extends Component
{
    public $examId;
    public $name;
    public $subject;
    public $classes = []; // Selected classes
    public $date;
    public $start_time;
    public $end_time;
    public $duration; // in minutes
    public $passing_grade = 70;
    
    // Settings
    public $shuffle_questions = false;
    public $shuffle_answers = false;
    public $tab_tolerance = 3;
    public $show_score = true;

    // Data Sources
    public $availableClasses = ['X IPA 1', 'X IPA 2', 'XI IPA 1', 'XI IPA 2', 'XII IPA 1', 'XII IPS 1'];
    
    // Filters
    public $filterSubject;
    
    // Step wizard (1: Details, 2: Questions)
    public $step = 1;

    public $token;

    public function mount($id = null)
    {
        if ($id) {
            $this->examId = $id;
            // Load dummy
            $this->name = 'Ujian Harian Matematika';
            $this->subject = 'Matematika';
            $this->classes = ['XI IPA 1'];
            $this->date = '2025-12-23';
            $this->start_time = '08:00';
            $this->end_time = '09:30';
            $this->duration = 90;
            $this->token = 'X7B29'; // Existing token
        } else {
            // New Exam
            $this->token = $this->generateToken();
        }
    }

    public function generateToken()
    {
        // Simple random 5-char alphanumeric token
        return strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 5));
    }
    
    public function regenerateToken()
    {
        $this->token = $this->generateToken();
    }

    public function toggleLevel($level)
    {
        // $level e.g., 'X', 'XI', 'XII'
        $levelClasses = array_filter($this->availableClasses, function($class) use ($level) {
            return str_starts_with($class, $level . ' ');
        });

        // Check if all are currently selected
        $allSelected = count(array_intersect($levelClasses, $this->classes)) === count($levelClasses);

        if ($allSelected) {
            // Deselect all
            $this->classes = array_values(array_diff($this->classes, $levelClasses));
        } else {
            // Select all (merge and unique)
            $this->classes = array_values(array_unique(array_merge($this->classes, $levelClasses)));
        }
    }

    public function render()
    {
        return view('teacher.exam.form')->extends('layouts.teacher')->section('content');
    }

    public function nextStep()
    {
        $this->step++;
    }

    public function prevStep()
    {
        $this->step--;
    }

    public function save()
    {
        return redirect()->route('teacher.exams.index');
    }
}
