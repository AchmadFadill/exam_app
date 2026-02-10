<?php

namespace App\Actions\Exam;

use App\Models\Exam;
use Illuminate\Support\Facades\DB;

class DuplicateExamAction
{
    public function execute(Exam $original): Exam
    {
        $original->loadMissing(['questions', 'classrooms']);

        return DB::transaction(function () use ($original): Exam {
            $newExam = $original->replicate();
            $newExam->name = 'Salinan - ' . $original->name;
            $newExam->status = 'draft';
            $newExam->date = now()->toDateString();
            $newExam->token = Exam::generateToken();
            $newExam->save();

            $questionsData = [];
            foreach ($original->questions as $question) {
                $questionsData[$question->id] = [
                    'order' => $question->pivot->order,
                    'score' => $question->pivot->score,
                ];
            }

            $newExam->questions()->sync($questionsData);
            $newExam->classrooms()->sync($original->classrooms->pluck('id')->all());

            return $newExam;
        });
    }
}
