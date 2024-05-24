<?php

namespace App\Repositories\UserAnswers;

use App\Models\Attestation;
use App\Models\Course;
use App\Models\LearningPath;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\UserQuestionAnswer;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserAnswersRepository
{
    use \App\Traits\PaginationParams;
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
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            throw new Exception(__('quiz_not_found'));
        }
        if ($quiz->is_exam) {
            $lastAttempt = $quiz->latestAttempt;
            if ($lastAttempt && !$this->canAttemptQuiz($lastAttempt)) {
                throw new Exception(__('quiz_already_attempted'));
            }
        }
        else {
            $lastAttempt = QuizAttempt::where('user_id', $userId)
                ->where('quiz_id', $quizId)
                ->latest('created_at')
                ->first();
            if ($lastAttempt && !$this->canAttemptQuiz($lastAttempt)) {
                $nextAttemptTime = $lastAttempt->created_at->addMinutes(0);
                $nextAttemptTime->diffInSeconds(now());
                throw new Exception(__('quiz_wait'), ResponseAlias::HTTP_FORBIDDEN, null);
            }
        }
        $score = 0;
        $totalScorePossible = 0;
        $needsReview = false;
        foreach ($answers as $answer) {
            $question = Question::find($answer['question_id']);
            if (!$question) {
                continue;
            }
            if ($question->type === 'OPEN') {
                $needsReview = true;
                $answer['answer'] = is_array($answer['answer']) ? implode(', ', $answer['answer']) : (string)$answer['answer'];
            } else {
                if (isset($answer['answer'])) {
                    $answer['answer'] = array_map('intval', (array)$answer['answer']);
                }

                $isCorrect = $this->checkAnswer($question, $answer['answer']);
                if ($isCorrect) {
                    $score += 1;
                }
            }
            $totalScorePossible++;

            UserQuestionAnswer::create([
                'user_id' => $userId,
                'quiz_id' => $quizId,
                'question_id' => $question->id,
                'answers' => $question->type === 'QCM' ? $answer['answer'] : null,
                'binary_answer' => $question->type === 'BINARY' ? $isCorrect : null,
                'open_answer' => $question->type === 'OPEN' ? $answer['answer'] : null,
                'needs_review' => $question->type === 'OPEN',
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

        if ($quiz->is_exam && !$needsReview) {
            $learningPathSubscription = DB::table('learning_path_subscriptions')->where('user_id', $userId)
                ->where('learning_path_id', $quiz->learning_path_id)
                ->first();
            $learningPathSubscription->is_completed = true;


            if ($passed) {
                $this->generateAttestation($userId, $quizId);
            } else {
                $learningPathSubscription->is_completed = 1;
            }

            DB::table('learning_path_subscriptions')
                ->where('id', $learningPathSubscription->id)
                ->update(['is_completed' => 1]);

        }

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

    /**
     * Validates an open question answer by the facilitator.
     * @param int $userAnswerId
     *@return UserQuestionAnswer
     * @throws Exception
     */
    public static function validateOpenQuestion(int $userAnswerId) : UserQuestionAnswer
    {
        $authUserId = auth()->id();
        $learningPaths = LearningPath::with('courses.facilitator')->get();
        $facilitatorIds = $learningPaths->flatMap(function($learningPath) {
            return $learningPath->courses->pluck('facilitator.id');
        })->toArray();

        if (!in_array($authUserId, $facilitatorIds)) {
            throw new Exception(__('unauthorized'));
        }
        $userAnswer = UserQuestionAnswer::findOrFail($userAnswerId);
        $userAnswer->is_validated = 1;
        $userAnswer->save();
        $quizAttempt = QuizAttempt::where('user_id', $userAnswer->user_id)
            ->where('quiz_id', $userAnswer->quiz_id)
            ->latest('created_at')
            ->first();
        $quiz = Quiz::find($userAnswer->quiz_id);
        $score = UserQuestionAnswer::where('quiz_id', $quiz->id)
            ->where('user_id', $userAnswer->user_id)
            ->where('is_validated', 1)
            ->count();
        $score += 1;

        $totalScorePossible = $quiz->questions()->count();
        $passingPercentage = 60;
        $userPercentage = ($score / $totalScorePossible) * 100;
        $passed = $userPercentage >= $passingPercentage;
        $openQuestions = $quiz->questions()->where('type', 'OPEN')->count();
        $validatedOpenQuestions = UserQuestionAnswer::where('quiz_id', $quiz->id)
            ->where('user_id', $userAnswer->user_id)
            ->where('is_validated', 1)
            ->count();

        if ($validatedOpenQuestions < $openQuestions) {
            $quizAttempt->needs_review = 1;
        } else {
            $quizAttempt->needs_review = 0;

            $learningPathSubscription = DB::table('learning_path_subscriptions')->where('user_id', $userAnswer->user_id)
                ->where('learning_path_id', $quiz->learning_path_id)
                ->first();
            if ($learningPathSubscription) {
                DB::table('learning_path_subscriptions')
                    ->where('id', $learningPathSubscription->id)
                    ->update(['is_completed' => 1]);
                if ($passed) {
                    self::generateAttestation($learningPathSubscription->learning_path_id, $userAnswer->user_id);
                }
            }
        }

        $quizAttempt->score = $score;
        $quizAttempt->passed = $passed;
        $quizAttempt->save();

        return $userAnswer;
    }

    /**
     * @throws Exception
     */
    public static function invalidateOpenQuestion($userAnswerId) : UserQuestionAnswer
    {
        $authUserId = auth()->id();
        $learningPaths = LearningPath::with('courses.facilitator')->get();
        $facilitatorIds = $learningPaths->flatMap(function($learningPath) {
            return $learningPath->courses->pluck('facilitator.id');
        })->toArray();

        if (!in_array($authUserId, $facilitatorIds)) {
            throw new Exception(__('unauthorized'));
        }

        $userAnswer = UserQuestionAnswer::findOrFail($userAnswerId);
        $userAnswer->is_validated = 0;
        $userAnswer->save();

        $quizAttempt = QuizAttempt::where('user_id', $userAnswer->user_id)
            ->where('quiz_id', $userAnswer->quiz_id)
            ->latest('created_at')
            ->first();

        $quiz = Quiz::find($userAnswer->quiz_id);
        $openQuestions = $quiz->questions()->where('type', 'OPEN')->count();
        $validatedOpenQuestions = UserQuestionAnswer::where('quiz_id', $quiz->id)
            ->where('user_id', $userAnswer->user_id)
            ->where('is_validated', 1)
            ->count();

        if ($validatedOpenQuestions < $openQuestions) {
            $quizAttempt->needs_review = 1;
        } else {
            // All open questions validated, check the score
            $quizAttempt->needs_review = 0;
            $passingPercentage = 60;
            $userPercentage = ($quizAttempt->score / $quizAttempt->total_score_possible) * 100;
            $quizAttempt->passed = $userPercentage >= $passingPercentage;

            $learningPathSubscription = DB::table('learning_path_subscriptions')->where('user_id', $userAnswer->user_id)
                ->where('learning_path_id', $quiz->learning_path_id)
                ->first();
            $learningPathSubscription->is_completed = 1;
            DB::table('learning_path_subscriptions')
                ->where('id', $learningPathSubscription->id)
                ->update(['is_completed' => 1]);

            if ($quizAttempt->passed) {
                self::generateAttestation($learningPathSubscription->learning_path_id, $userAnswer->user_id);
            }
        }
        $quizAttempt->save();
        return $userAnswer;
    }

    private static function generateAttestation($learningPathId, $userId) : Attestation
    {

        return Attestation::create([
            'user_id' => $userId,
            'learning_path_id' => $learningPathId,
            'created_at' => now()->timestamp,
        ]);
    }
    public final function downloadAttestation($learningPathId) : \Illuminate\Http\Response
    {
        $userId = auth()->id();
        $learningPath = LearningPath::findOrFail($learningPathId);
        $user = User::findOrFail($userId);

        $data = [
            'user_name' => $user->first_name . ' ' . $user->last_name,
            'learning_path_title' => $learningPath->title,
            'date' => now()->format('F j, Y')
        ];

        $pdf = Pdf::loadView('attestations.template', $data);
        return $pdf->download('attestation.pdf');
    }
    public static function indexPendingOpenQuestionAnswers($queryConfig) : LengthAwarePaginator | Collection
    {
        $authUserId = auth()->id();
        $courses = Course::where('facilitator_id', $authUserId)->pluck('id');

        $learningPaths = Course::whereIn('id', $courses)
            ->with(['learningPaths' => function($query) {
                $query->with(['quiz' => function($query) {
                    $query->with(['questions' => function($query) {
                        $query->where('type', 'OPEN');
                    }]);
                }]);
            }])->get()->pluck('learningPaths')->flatten()->unique('id');
        $questions = $learningPaths->pluck('quiz.questions')->flatten()->unique('id');
        $answersQuery = UserQuestionAnswer::whereIn('question_id', $questions->pluck('id'))
            ->whereNull('is_validated')
            ->join('questions', 'user_question_answers.question_id', '=', 'questions.id')
            ->select('user_question_answers.*', 'questions.question')
            ->get();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($answersQuery, $queryConfig);
        }
        return $answersQuery;
    }
}

