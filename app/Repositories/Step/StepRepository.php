<?php

namespace App\Repositories\Step;

use App\Models\Answer;
use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Step;
use App\Repositories\Media\MediaRepository;
use Exception;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class StepRepository
{

    /**
     * @param $data
     * @param $course_id
     * @return Step
     * @throws Exception
     */
    public final function createStep($data, $course_id): Step
    {
        $step = null;
        $course = Course::find($course_id);
        if (!$course) {
            throw new Exception(__('course_not_found'));
        }
        foreach ($data['steps'] as $stepData) {
            // Create the step and attach it to the course
            $step = $course->steps()->create([
                'title' => $stepData['title'],
                'description' => $stepData['description'],
                'duration' => $stepData['duration'],
            ]);

            if (!empty($stepData['media_files'])) {
                self::processMediaFiles($step, $stepData['media_files'], $stepData['media_titles'] ?? []);
            }

            if (!empty($stepData['media_urls'])) {
                self::processMediaUrls($step, $stepData['media_urls']);
            }

            if (isset($stepData['quiz'])) {

                self::createQuizForStep($step, $stepData['quiz']);
            }
        }
        return $step;
    }

    /**
     * @param $step
     * @param $quizData
     * @return void
     */

    private static function createQuizForStep($step, $quizData): void
    {


        $quiz = $step->quiz()->create(['is_exam' => false]);

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
     * @param $step
     * @param $mediaFiles
     * @return void
     */
    private static function processMediaFiles($step, $mediaFiles): void
    {
        foreach ($mediaFiles as $file) {
            $title = $file->getClientOriginalName();
            MediaRepository::attachOrUpdateMediaForModel($step, $file, null, $title);
        }
    }

    /**
     * @param $step
     * @param $mediaUrls
     * @return void
     */
    private static function processMediaUrls($step, $mediaUrls): void
    {
        foreach ($mediaUrls as $mediaUrl) {
            $step->media()->create([
                'external_url' => $mediaUrl['url'],
                'title' => $mediaUrl['title'] ?? null,
            ]);
        }
    }

    /**
     * @param $courseId
     * @param $stepId
     * @param $data
     * @return Step
     * @throws Exception
     */
    public final function updateCourseStep($stepId, $data): Step
    {
        $user = auth()->user();
        $step = Step::findOrFail($stepId);
        if ($step->course->added_by != $user->id) {
            throw new Exception(__('user_not_authorized'), ResponseAlias::HTTP_FORBIDDEN);
        }

        if (!$step) {
            throw new Exception(__('step_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }


        $files = $data['media_files'] ?? null;
        $mediaToRemove = $data['media_to_remove'] ?? null;
        $externalUrls = $data['media_urls'] ?? null;

        $this->updateStepMedia($step, $files, $mediaToRemove, $externalUrls);

        $step->fill($data)->save();


        return $step;
    }

    /**
     * @param $step
     * @param $files
     * @param $mediaToRemove
     * @param $externalUrls
     * @return void
     */
    private function updateStepMedia($step, $files, $mediaToRemove, $externalUrls): void
    {
        if (!empty($mediaToRemove)) {
            foreach ($mediaToRemove as $mediaId) {
                MediaRepository::detachMediaFromModel($step, $mediaId);
            }
        }
        if (!empty($files)) {
            foreach ($files as $file) {
                MediaRepository::attachOrUpdateMediaForModel($step, $file, null, $file->getClientOriginalName());
            }
        }
        if (!empty($externalUrls)) {
            foreach ($externalUrls as $url) {
                $step->media()->create([
                    'external_url' => $url,
                    'title' => $url['title'] ?? null,
                ]);
            }
        }
    }


    /**
     * @throws Exception
     */
    public final function updateQuizForStep($stepId, $quizData): Quiz
    {
        $user = auth()->user();
        $step = Step::findOrFail($stepId);
        $owner = Course::where('id', $step->course_id)->value('added_by');

        if ($user->id !== $owner) {
            throw new Exception(__('user_not_authorized'));
        }
        $quiz = $step->quiz()->firstOrCreate(['is_exam' => false], ['step_id' => $step->id]);
        $quizData = $quizData['quiz'];

        if (isset($quizData['questions']) && is_array($quizData['questions'])) {

            foreach ($quizData['questions'] as $questionData) {
                if (!empty($questionData['id'])) {

                    // Update existing question
                    $this->updateQuizQuestion($questionData, $questionData['id']);
                } else {
                    // Add new question
                    $question = $quiz->questions()->create($questionData);
                    // Add answers for the new question
                    if (isset($questionData['answers']) && is_array($questionData['answers'])) {
                        foreach ($questionData['answers'] as $answerData) {
                            $question->answers()->create([
                                'answer' => $answerData['answer'],
                                'is_valid' => $answerData['is_valid'] ?? false,
                            ]);
                        }
                    }
                }
            }
        }

        if (isset($quizData['questions_to_remove']) && is_array($quizData['questions_to_remove'])) {

            foreach ($quizData['questions_to_remove'] as $questionId) {

                Question::findOrFail($questionId)->delete();

            }
        }
        if (isset($quizData['answers_to_remove']) && is_array($quizData['answers_to_remove'])) {

            Answer::destroy($quizData['answers_to_remove']);


        }
        return $quiz;
    }

    /**
     * @param $questionData
     * @param $questionId
     * @return void
     * @throws Exception
     */
    private function updateQuizQuestion($questionData, $questionId): void
    {
        $question = Question::findOrFail($questionId);
        if (!$question) {
            throw new Exception(__('question_not_found'));
        }
        if (isset($questionData['answers']) && is_array($questionData['answers'])) {
            $this->updateQuestionAnswers($question, $questionData['answers']);
        }
        if (!empty($questionData)) {
            $question->fill($questionData)->save();
        }

    }

    /**
     * @param $question
     * @param $answersData
     * @return void
     */
    private function updateQuestionAnswers($question, $answersData): void
    {


        foreach ($answersData as $answerData) {
            if (isset($answerData['id'])) {
                $answer = $question->answers()->find($answerData['id']);
                if ($answer) {
                    $answer->update([
                        'answer' => $answerData['answer'],
                        'is_valid' => $answerData['is_valid'] ?? false,
                    ]);
                }
            } else {
                $question->answers()->create([
                    'answer' => $answerData['answer'],
                    'is_valid' => $answerData['is_valid'] ?? false,
                ]);
            }
        }

    }

    /**
     * @param $course_id
     * @return Collection
     * @throws Exception
     */
    public final function showCourseSteps($course_id): Collection
    {
        $user = auth()->user();
        $course = Course::with([
            'steps',
            'steps.media',
            'steps.quiz',
            'steps.quiz.questions',
            'steps.quiz.questions.answers'
        ])->where('added_by', $user->getAuthIdentifier())->findOrFail($course_id);

        if (!$course) {
            throw new Exception(__('course_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }

        return $course->steps;
    }

    /**
     * delete a step with its relations from the course
     * @param $stepId
     * @throws Exception
     */
    public final function deleteStepFromCourse($stepId): void
    {
        $step = Step::find($stepId);
        if (!$step) {
            throw new Exception(__('step_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
        $step->deleteWithRelations();
    }

    /**
     * @param $stepId
     * @return void
     * @throws Exception
     */
    public final function deleteQuizFromStep($stepId): void
    {
        $step = Step::findOrFail($stepId);

        if ($quiz = $step->quiz) {
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

}
