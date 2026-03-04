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
                ->withTrashed()
                ->where('question_id', $question->id)
                ->first();

            if ($option && $option->is_correct) {
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
                $canDetermine = true;
                $isCorrect = false;
                $normalizedSelectedOptionId = $answer->selected_option_id ? (int) $answer->selected_option_id : null;
                $selectedOption = $this->resolveSelectedOptionById(
                    questionId: (int) $answer->question_id,
                    selectedOptionId: $answer->selected_option_id,
                    rawAnswer: is_string($answer->answer) ? $answer->answer : null,
                );

                if ($selectedOption) {
                    $normalizedSelectedOptionId = (int) $selectedOption->id;
                }

                if ($selectedOption) {
                    $isCorrect = (bool) $selectedOption->is_correct;
                } else {
                    $canDetermine = false;
                }

                $scoreAwarded = $canDetermine
                    ? ($isCorrect ? (int) $meta['score'] : 0)
                    : (int) ($answer->score_awarded ?? 0);

                $updates = [];

                if ($normalizedSelectedOptionId !== null && (int) ($answer->selected_option_id ?? 0) !== $normalizedSelectedOptionId) {
                    $updates['selected_option_id'] = $normalizedSelectedOptionId;
                    // Keep existing app convention for PG: answer stores selected option id string.
                    $updates['answer'] = (string) $normalizedSelectedOptionId;
                }

                if ($canDetermine && ((bool) $answer->is_correct !== $isCorrect || (int) $answer->score_awarded !== $scoreAwarded)) {
                    $updates = array_merge($updates, [
                        'is_correct' => $isCorrect,
                        'score_awarded' => $scoreAwarded,
                    ]);
                }

                if (!empty($updates)) {
                    $answer->update($updates);
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

    private function resolveSelectedOptionById(int $questionId, mixed $selectedOptionId, ?string $rawAnswer = null): ?QuestionOption
    {
        if ($selectedOptionId) {
            $option = QuestionOption::query()
                ->where('id', (int) $selectedOptionId)
                ->withTrashed()
                ->where('question_id', $questionId)
                ->first();

            if ($option) {
                return $option;
            }
        }

        $raw = trim((string) $rawAnswer);
        if ($raw !== '' && is_numeric($raw)) {
            return QuestionOption::query()
                ->where('id', (int) $raw)
                ->withTrashed()
                ->where('question_id', $questionId)
                ->first();
        }

        return null;
    }

    private function examPivotScore(Exam $exam, int $questionId): int
    {
        $question = $exam->questions()
            ->where('questions.id', $questionId)
            ->first();

        return (int) ($question?->pivot?->score ?? 0);
    }

}
