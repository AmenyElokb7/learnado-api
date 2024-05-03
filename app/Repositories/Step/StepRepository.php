<?php

namespace App\Repositories\Step;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\Step;
use App\Repositories\Media\MediaRepository;
use App\Repositories\Quiz\QuizRepository;
use Exception;
use Illuminate\Support\Facades\DB;
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
     * Update a step with new information, media files, and external URLs.
     *
     * @param int $stepId The ID of the step to update.
     * @param array $data The new data for the step.
     * @return Step The updated step.
     * @throws Exception If an error occurs.
     */
    public final function updateStep(int $stepId, array $data): Step
    {
        DB::beginTransaction();
        try{

        $user = auth()->user();
            $step = Step::with(['media'])->findOrFail($stepId);
        if ($step->course->added_by != $user->id) {
            throw new Exception(__('user_not_authorized'), ResponseAlias::HTTP_FORBIDDEN);
        }
        if (!$step) {
            throw new Exception(__('step_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }

            $step->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'duration' => $data['duration'],
            ]);


        $existingMediaIds = $step->media()->pluck('id')->toArray();

        // Remove media files that are no longer attached to the step and put them in an array to delete
        $mediaFilesToDelete = array_diff($existingMediaIds, $data['media_files']);



        // Add new media files
        if (!empty($data['media_files'])) {
            self::processMediaFiles($step, $data['media_files']);
        }


        // Remove external URLs that are no longer attached to the step
        foreach ($step->media()->whereNotNull('external_url')->get() as $media) {
            if (!in_array($media->id, $data['external_urls'])) {
                $media->delete();
            }
        }
        // Update existing external URLs and add new ones
        foreach ($data['external_urls'] as $externalUrlData) {
            if (isset($externalUrlData['id'])) {
                // Update existing media record
                $media = $step->media()->find($externalUrlData['id']);
                $media->update([
                    'title' => $externalUrlData['title'],
                    'external_url' => $externalUrlData['url']
                ]);
            } else {
                // Create new media record for the external URL
                MediaRepository::attachOrUpdateMediaForModel($step, null, null, $externalUrlData['title']);

            }
        }
            DB::commit();
            return $step;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
