<?php

namespace App\Livewire\Teacher\QuestionBank;

use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public $questionId;
    public $type = 'multiple_choice'; // multiple_choice, essay
    public $question_text;
    public $question_image;
    public $subject;
    public $points = 10;
    
    // Multiple Choice Specifics
    public $options = [
        ['text' => '', 'is_correct' => false],
        ['text' => '', 'is_correct' => false],
        ['text' => '', 'is_correct' => false],
        ['text' => '', 'is_correct' => false],
        ['text' => '', 'is_correct' => false],
    ];
    public $correct_answer_index = null;
    public $explanation;

    // Essay Specifics
    public $answer_key; // For grading guide

    public function mount($id = null)
    {
        if ($id) {
            $this->questionId = $id;
            // Load dummy data
            $this->question_text = 'Apa ibukota Indonesia?';
            $this->subject = 'Geografi';
            $this->type = 'multiple_choice';
            $this->options[0]['text'] = 'Jakarta';
            $this->options[0]['is_correct'] = true;
            $this->correct_answer_index = 0;
            // ...
        }
    }

    public function setCorrectAnswer($index)
    {
        $this->correct_answer_index = $index;
        foreach ($this->options as $key => $option) {
            $this->options[$key]['is_correct'] = ($key == $index);
        }
    }

    public function render()
    {
        return view('teacher.question-bank.form')->extends('layouts.teacher')->section('content');
    }

    public function save()
    {
        // Dummy save
        return redirect()->route('teacher.question-bank.index');
    }
}
