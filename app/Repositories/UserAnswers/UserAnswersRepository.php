<?php

namespace App\Repositories\UserAnswers;

use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\UserQuestionAnswer;
use Exception;

class UserAnswersRepository
{
    /**
     * Submits quiz answers, calculates scores, and handles answers that need review.
     * @param int $userId
     * @param int $quizId
     * @param array $answers
     * @return array
     * @throws Exception
     */
    public final function submitQuizAnswers($userId, $quizId, $answers): array
    {
        $lastAttempt = QuizAttempt::where('user_id', $userId)
            ->where('quiz_id', $quizId)
            ->latest('created_at')
            ->first();

        if ($lastAttempt && !$this->canAttemptQuiz($lastAttempt)) {
            $nextAttemptTime = $lastAttempt->created_at->addMinutes(0);
            $nextAttemptTime->diffInSeconds(now());

            throw new Exception('Please wait 30 minutes before attempting this quiz again.', 0, null);
        }

        $score = 0;
        $totalScorePossible = 0;
        $needsReview = false;

        foreach ($answers as &$answer) {
            $question = Question::find($answer['question_id']);
            if (!$question) {
                continue;
            }

            if (isset($answer['answer'])) {
                $answer['answer'] = array_map('intval', (array)$answer['answer']);
            }
            $isCorrect = false;
            if ($question->type === 'open') {
                $needsReview = true;
            } else {
                $totalScorePossible++;
                $isCorrect = $this->checkAnswer($question, $answer['answer']);
                if ($isCorrect) {
                    $score += 1;
                }
            }

            UserQuestionAnswer::create([
                'user_id' => $userId,
                'quiz_id' => $quizId,
                'question_id' => $question->id,
                'answers' => $question->type === 'QCM' ? $answer['answer'] : null,
                'binary_answer' => $question->type === 'BINARY' ? $isCorrect : null,
                'open_answer' => $question->type === 'open' ? $answer['answer'] : null,
                'needs_review' => $question->type === 'open',
            ]);
        }
        unset($answer);

        $passed = false;
        if (!$needsReview) {
            $passingPercentage = 60;
            $userPercentage = ($score / $totalScorePossible) * 100;
            $passed = $userPercentage >= $passingPercentage;
        }

        QuizAttempt::create([
            'user_id' => $userId,
            'quiz_id' => $quizId,
            'score' => $score,
            'total_score_possible' => $totalScorePossible,
            'needs_review' => $needsReview,
            'passed' => $passed,
        ]);

        return [
            'score' => $score,
            'total_score_possible' => $totalScorePossible,
            'needs_review' => $needsReview,
            'passed' => $passed,
        ];
    }

    /**
     * Check if the provided answer is correct.
     * @param Question $question
     * @param $userAnswer
     * @return bool
     */
    protected function checkAnswer(Question $question, $userAnswer): bool
    {
        if ($question->type === 'BINARY') {
            return $question->is_valid == $userAnswer[0];
        } else if ($question->type === 'QCM') {
            $correctAnswers = $question->answers()->where('is_valid', true)->pluck('id')->toArray();
            sort($userAnswer);
            sort($correctAnswers);
            return $correctAnswers === $userAnswer;
        }
        return false;
    }
    /**
     * Determines if a new quiz attempt can be made based on the last attempt time.
     * @param QuizAttempt $lastAttempt
     * @return bool
     */
    protected function canAttemptQuiz(QuizAttempt $lastAttempt): bool
    {
        return now()->subMinutes(30) > $lastAttempt->created_at;
    }
}

