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
                $canDetermine = true;
                $isCorrect = false;
                $normalizedSelectedOptionId = $answer->selected_option_id ? (int) $answer->selected_option_id : null;
                $selectedOption = null;

                // 1) Resolve selected option from FK; if mismatched question, remap by label.
                if ($answer->selected_option_id) {
                    $rawSelected = QuestionOption::query()
                        ->where('id', $answer->selected_option_id)
                        ->first();

                    if ($rawSelected && (int) $rawSelected->question_id === (int) $answer->question_id) {
                        $selectedOption = $rawSelected;
                    } elseif ($rawSelected) {
                        $remapped = QuestionOption::query()
                            ->where('question_id', $answer->question_id)
                            ->where('label', $rawSelected->label)
                            ->first();

                        if ($remapped) {
                            $selectedOption = $remapped;
                            $normalizedSelectedOptionId = (int) $remapped->id;
                        }
                    }
                }

                // 2) Resolve from legacy/raw answer value when selected option is missing/stale.
                if (!$selectedOption && is_string($answer->answer)) {
                    $raw = trim($answer->answer);

                    if ($raw !== '') {
                        if (is_numeric($raw)) {
                            $candidate = QuestionOption::query()
                                ->where('id', (int) $raw)
                                ->where('question_id', $answer->question_id)
                                ->first();

                            if ($candidate) {
                                $selectedOption = $candidate;
                                $normalizedSelectedOptionId = (int) $candidate->id;
                            }
                        }

                        if (!$selectedOption && preg_match('/^[A-E]$/i', $raw) === 1) {
                            $candidate = QuestionOption::query()
                                ->where('question_id', $answer->question_id)
                                ->where('label', strtoupper($raw))
                                ->first();

                            if ($candidate) {
                                $selectedOption = $candidate;
                                $normalizedSelectedOptionId = (int) $candidate->id;
                            }
                        }

                        if (!$selectedOption) {
                            $candidate = QuestionOption::query()
                                ->where('question_id', $answer->question_id)
                                ->get()
                                ->first(fn (QuestionOption $opt) => $this->answersMatch($raw, $opt->text));

                            if ($candidate) {
                                $selectedOption = $candidate;
                                $normalizedSelectedOptionId = (int) $candidate->id;
                            }
                        }
                    }
                }

                if ($selectedOption) {
                    $isCorrect = (bool) $selectedOption->is_correct;
                } elseif (is_string($answer->answer)) {
                    $correctOption = QuestionOption::query()
                        ->where('question_id', $answer->question_id)
                        ->where('is_correct', true)
                        ->first();

                    // If stored answer is numeric id-like string and no option can be resolved,
                    // treat as indeterminate instead of forcing it to wrong.
                    if (is_numeric(trim($answer->answer))) {
                        $canDetermine = false;
                    } elseif ($correctOption) {
                        $isCorrect = $this->answersMatch($answer->answer, $correctOption->text);
                    } else {
                        $canDetermine = false;
                    }
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
