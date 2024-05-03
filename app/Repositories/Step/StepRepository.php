<?php

namespace App\Repositories\Step;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\Step;
use App\Repositories\Media\MediaRepository;
use App\Repositories\Quiz\QuizRepository;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class StepRepository
{

    /** Add steps to a course
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

                QuizRepository::createQuiz($step, $stepData['quiz']);
            }
        }
        return $step;
    }


    /**
     * add media files to a step
     * @param $step
     * @param $mediaFiles
     * @return void
     */
    private static function processMediaFiles($step, $mediaFiles): void
    {
        foreach ($mediaFiles as $file) {
            $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            MediaRepository::attachOrUpdateMediaForModel($step, $file, null, $title);
        }
    }

    /**
     * add media urls to a step
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
     *
     * @param $stepId
     * @param $data
     * @return Step
     * @throws Exception
     */
    public final function updateStep($stepId, $data): Step
    {
        $user = auth()->user();
        $step = Step::findOrFail($stepId);
        if ($step->course->added_by != $user->id) {
            throw new Exception(__('user_not_authorized'), ResponseAlias::HTTP_FORBIDDEN);
        }
        if (!$step) {
            throw new Exception(__('step_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
        $step->update($data);
        return $step;
    }

    /**
     * @throws Exception
     */
    public function getStepMediaById($stepId)
    {
        $step = Step::find($stepId);
        if(!$step){
            throw new Exception(__('step_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
        return $step->load('media');
    }

    public final function deleteStep($stepId): void
    {
        $step = Step::findOrFail($stepId);
        $step->deleteWithRelations();
    }
}
