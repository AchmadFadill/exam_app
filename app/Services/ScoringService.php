<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;

class ScoringService
{
    public function scoreSingleAnswer(Exam $exam, Question $question, mixed $answerValue): array
    {
        if ($question->type === 'essay') {
            return [
                'answer' => (string) $answerValue,
                'selected_option_id' => null,
                'is_correct' => null,
                'score_awarded' => null,
            ];
        }

        $isOptionId = is_numeric($answerValue);
        $selectedOptionId = $isOptionId ? (int) $answerValue : null;

        $isCorrect = false;
        $scoreAwarded = 0;

        if ($selectedOptionId) {
            $option = QuestionOption::where('id', $selectedOptionId)
                ->where('question_id', $question->id)
                ->first();

            if ($option && $option->is_correct) {
                $isCorrect = true;
                $scoreAwarded = $this->examPivotScore($exam, $question->id);
            }
        } elseif (is_string($answerValue)) {
            $correctOption = QuestionOption::query()
                ->where('question_id', $question->id)
                ->where('is_correct', true)
                ->first();

            if ($correctOption && $this->answersMatch($answerValue, $correctOption->text)) {
                $isCorrect = true;
                $scoreAwarded = $this->examPivotScore($exam, $question->id);
            }
        }

        return [
            'answer' => (string) $answerValue,
            'selected_option_id' => $selectedOptionId,
            'is_correct' => $isCorrect,
            'score_awarded' => $scoreAwarded,
        ];
    }

    public function recalculateAttempt(Exam $exam, ExamAttempt $attempt): array
    {
        $examQuestions = $exam->questions()
            ->get(['questions.id', 'questions.type']);

        $questionMeta = $examQuestions->mapWithKeys(function ($question) {
            return [
                (int) $question->id => [
                    'type' => $question->type,
                    'score' => (int) ($question->pivot->score ?? 0),
                ],
            ];
        });

        $answers = StudentAnswer::where('exam_attempt_id', $attempt->id)->get();
        $totalScore = 0;

        foreach ($answers as $answer) {
            $meta = $questionMeta->get((int) $answer->question_id);
            if (!$meta) {
                continue;
            }

            if ($meta['type'] === 'multiple_choice') {
                $isCorrect = false;

                if ($answer->selected_option_id) {
                    $isCorrect = QuestionOption::query()
                        ->where('id', $answer->selected_option_id)
                        ->where('question_id', $answer->question_id)
                        ->where('is_correct', true)
                        ->exists();
                } elseif (is_string($answer->answer)) {
                    $correctOption = QuestionOption::query()
                        ->where('question_id', $answer->question_id)
                        ->where('is_correct', true)
                        ->first();

                    $isCorrect = $correctOption
                        ? $this->answersMatch($answer->answer, $correctOption->text)
                        : false;
                }

                $scoreAwarded = $isCorrect ? (int) $meta['score'] : 0;

                if ((bool) $answer->is_correct !== $isCorrect || (int) $answer->score_awarded !== $scoreAwarded) {
                    $answer->update([
                        'is_correct' => $isCorrect,
                        'score_awarded' => $scoreAwarded,
                    ]);
                }

                $totalScore += $scoreAwarded;

                continue;
            }

            $totalScore += (int) ($answer->score_awarded ?? 0);
        }

        $maxScore = (int) $questionMeta->sum('score');
        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;
        $hasEssay = $questionMeta->contains(fn ($meta) => $meta['type'] === 'essay');

        return [
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'passed' => $percentage >= $exam->passing_grade,
            'has_essay' => $hasEssay,
        ];
    }

    private function examPivotScore(Exam $exam, int $questionId): int
    {
        $question = $exam->questions()
            ->where('questions.id', $questionId)
            ->first();

        return (int) ($question?->pivot?->score ?? 0);
    }

    private function answersMatch(?string $studentAnswer, ?string $correctAnswer): bool
    {
        return strtoupper(trim((string) $studentAnswer)) === strtoupper(trim((string) $correctAnswer));
    }
}
