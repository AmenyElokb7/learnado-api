<?php

namespace App\Repositories\Quiz;


use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Ramsey\Collection\Collection;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class QuizRepository
{

    public static function createQuiz($entity, $quizData, $isExam = false): void
    {
        $quiz = $entity->quiz()->create(['is_exam' => $isExam]);

        foreach ($quizData['questions'] as $questionData) {
            self::createQuestionForQuiz($quiz, $questionData);
        }
    }

    /**
     * @param $quiz
     * @param $questionData
     * @return void
     */

    public static function createQuestionForQuiz($quiz, $questionData): void
    {
        $question = $quiz->questions()->create([
            'question' => $questionData['question'],
            'type' => $questionData['type'],
            'is_valid' => $questionData['is_valid'] ?? null, // Only applicable for binary questions
        ]);

        if (isset($questionData['answers']) && $questionData['type'] !== 'open') {
            foreach ($questionData['answers'] as $answerData) {
                self::createAnswerForQuestion($question, $answerData);
            }
        }
    }

    /**
     * @param $question
     * @param $answerData
     * @return void
     */
    public static function createAnswerForQuestion($question, $answerData): void
    {
        $question->answers()->create([
            'answer' => $answerData['answer'],
            'is_valid' => $answerData['is_valid'] ?? null,
        ]);

    }


    /**
     * @throws Exception
     */
    public static function updateQuiz($step_id, $quizData): Collection|Quiz|Builder
    {
        DB::beginTransaction();
        try {
            $quiz = Quiz::where('step_id', $step_id)->first();

            if (!$quiz) {
                throw new Exception(__('quiz_not_found'), ResponseAlias::HTTP_NOT_FOUND);
            }
            $quizData = $quizData['quiz'];

            // Process each question provided in the request
            if (isset($quizData['questions']) && is_array($quizData['questions'])) {

                foreach ($quizData['questions'] as $questionData) {
                    if (isset($questionData['id'])) {
                        $question= $quiz->questions()->find($questionData['id']);

                        // Update existing question
                        self::updateOrCreateQuestion($quiz, $questionData, $questionData['id']);
                    } else {
                        // Add new question
                        self::createQuestionWithAnswers($quiz, $questionData);
                    }
                }
            }

            DB::commit();
            return $quiz;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * @param $quiz
     * @param $questionData
     * @param null $questionId
     * @return void
     */
    private static function updateOrCreateQuestion($quiz, $questionData, $questionId = null): void
    {
        $question = null;
        if ($questionId) {
            $question = $quiz->questions()->findOrFail($questionId);


            // If changing from QCM to BINARY, delete all existing answers.
            if ($question->type === 'QCM' && $questionData['type'] === 'BINARY') {

                $question->answers()->delete(); // This deletes all related answers.
            }  else if ($question->type === 'BINARY' && $questionData['type'] === 'QCM') {
                    $question->update(['is_valid' => null]);
                }

            // Update the question itself.
            $question->update($questionData);
        } else {
            $question = $quiz->questions()->create($questionData);
        }

        // If the question is of type BINARY, I don't handle answers here because they should be deleted above.
        // Only if it's QCM, we update or create new answers.
        if ($questionData['type'] === 'QCM' && isset($questionData['answers']) && is_array($questionData['answers'])) {
            foreach ($questionData['answers'] as $answerData) {
                if (isset($answerData['id'])) {
                    $answer = $question->answers()->find($answerData['id']);
                    if ($answer) {
                        // Update existing answer
                        $answer->update($answerData);
                    }
                } else {
                    // Create new answer
                    $question->answers()->create($answerData);
                }
            }
        }
    }

    private static function createQuestionWithAnswers($quiz, $questionData): void
    {
        $question = $quiz->questions()->create($questionData);
        if (isset($questionData['answers'])) {
            foreach ($questionData['answers'] as $answerData) {
                // Create new answer
                $question->answers()->create($answerData);
            }
        }
    }


    /**
     * Deletes the quiz associated with the given entity (Step or Learning Path).
     *
     * @param $quiz_id
     * @throws Exception If the quiz is not found.
     */
    public static function deleteQuiz($quiz_id): void
    {
        $quiz = Quiz::findOrFail($quiz_id);
        if ($quiz) {
            // Delete all answers to each question
            foreach ($quiz->questions as $question) {
                $question->answers()->delete();
            }
            // Delete all questions
            $quiz->questions()->delete();

            // Delete the quiz
            $quiz->delete();
        } else {
            throw new Exception(__('quiz_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    /**
     * @throws Exception
     */
    public static function deleteQuestion($question_id): void
    {
        $question = Question::findOrFail($question_id);
        if ($question) {
            // Delete all answers to the question
            $question->answers()->delete();
            // Delete the question
            $question->delete();
        } else {
            throw new Exception(__('question_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    /**
     * @throws Exception
     */
    public static function deleteAnswer($answer_id): void
    {
        $answer = Answer::findOrFail($answer_id);
        if ($answer) {
            $answer->delete();
        } else {
            throw new Exception(__('answer_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
    }


}
