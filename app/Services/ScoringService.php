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
        $raw = trim((string) $rawAnswer);

        if ($selectedOptionId) {
            $option = QuestionOption::query()
                ->where('id', (int) $selectedOptionId)
                ->withTrashed()
                ->first();

            // Canonical path:
            // if selected_option_id already points to this question,
            // never override it with legacy raw-answer inference.
            if ($option && (int) $option->question_id === $questionId) {
                return $option;
            }

            // Legacy recovery: selected option id points to another question.
            // Remap by the same label within the target question (A/B/C/D/E).
            if ($option && !empty($option->label)) {
                $sameLabel = QuestionOption::query()
                    ->where('question_id', $questionId)
                    ->withTrashed()
                    ->where('label', $option->label)
                    ->get();

                if ($sameLabel->count() === 1) {
                    return $sameLabel->first();
                }
            }
        }

        if ($raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            $numericMatch = QuestionOption::query()
                ->where('id', (int) $raw)
                ->withTrashed()
                ->where('question_id', $questionId)
                ->first();

            if ($numericMatch) {
                return $numericMatch;
            }
        }

        // Legacy fallback: label answer (A-E), still constrained to the same question.
        if (preg_match('/^[A-E]$/i', $raw) === 1) {
            $labelMatch = QuestionOption::query()
                ->where('question_id', $questionId)
                ->withTrashed()
                ->where('label', strtoupper($raw))
                ->first();

            if ($labelMatch) {
                return $labelMatch;
            }
        }

        // Legacy fallback: full option text saved in answer column.
        // To avoid false positives when duplicated text exists, only accept unique match.
        $textMatches = QuestionOption::query()
            ->where('question_id', $questionId)
            ->withTrashed()
            ->get()
            ->filter(function (QuestionOption $option) use ($raw): bool {
                return strtoupper(trim((string) $option->text)) === strtoupper($raw);
            })
            ->values();

        if ($textMatches->count() === 1) {
            return $textMatches->first();
        }

        return null;
    }

    private function resolveFromAttemptOptionOrder(
        int $questionId,
        string $raw,
        ?ExamAttempt $attempt,
        ?Exam $exam
    ): ?QuestionOption {
        if (!$attempt || !$exam || !$exam->shuffle_answers) {
            return null;
        }

        $optionIds = $attempt->options_order[(string) $questionId] ?? null;
        if (!is_array($optionIds) || empty($optionIds)) {
            return null;
        }

        $indexes = [];
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        // Numeric legacy value can be either 1-based or 0-based position.
        if (is_numeric($raw)) {
            $n = (int) $raw;
            $indexes[] = $n - 1;
            $indexes[] = $n;
        }

        // Label legacy value A/B/C... can also represent rendered position.
        if (preg_match('/^[A-Z]$/i', $raw) === 1) {
            $indexes[] = ord(strtoupper($raw)) - 65;
        }

        $indexes = array_values(array_unique(array_filter($indexes, fn ($idx) => $idx >= 0)));
        if (empty($indexes)) {
            return null;
        }

        foreach ($indexes as $idx) {
            if (!array_key_exists($idx, $optionIds)) {
                continue;
            }

            $candidateOptionId = (int) $optionIds[$idx];
            $candidate = QuestionOption::query()
                ->where('id', $candidateOptionId)
                ->where('question_id', $questionId)
                ->withTrashed()
                ->first();

            if ($candidate) {
                return $candidate;
            }
        }

        return null;
    }

    private function isLikelyPositionalRaw(string $raw): bool
    {
        $raw = trim($raw);
        if ($raw === '') {
            return false;
        }

        if (preg_match('/^[A-Z]$/i', $raw) === 1) {
            return true;
        }

        if (!is_numeric($raw)) {
            return false;
        }

        // Position-based answers are usually small integers (0..10).
        // Large integers are likely true option IDs.
        $n = (int) $raw;

        return $n >= 0 && $n <= 10;
    }

    private function examPivotScore(Exam $exam, int $questionId): int
    {
        $question = $exam->questions()
            ->where('questions.id', $questionId)
            ->first();

        return (int) ($question?->pivot?->score ?? 0);
    }

}
