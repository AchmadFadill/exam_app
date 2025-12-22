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

    // Step wizard (1: Details, 2: Questions)
    public $step = 1;

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
