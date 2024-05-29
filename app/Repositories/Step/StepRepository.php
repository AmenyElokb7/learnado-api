<?php

namespace App\Repositories\Step;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\Step;
use App\Repositories\Media\MediaRepository;
use App\Repositories\Quiz\QuizRepository;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            $step = $course->steps()->create([
                'title' => $stepData['title'],
                'description' => $stepData['description'],
                'duration' => $stepData['duration'],
            ]);
            if (!empty($stepData['media_files'])) {
                self::processMediaFiles($step, $stepData['media_files'], $stepData['media_titles'] ?? []);
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
     * Update a step with new information, media files.
     *
     * @param int $stepId The ID of the step to update.
     * @param array $data The new data for the step.
     * @return Builder|Collection|Model|Builder[] The updated step.
     * @throws Exception If an error occurs.
     */
    public final function updateStep(int $stepId, array $data): Builder|array|Collection|Model
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
        if (!empty($data['media_files'])) {
            $data['media_files'] = array_filter($data['media_files'], function ($file) {
                return !Str::contains($file, 'http');
            });
            self::processMediaFiles($step, $data['media_files']);
        }
        if (isset($data['deleted_media'])) {
            foreach ($data['deleted_media'] as $mediaName) {
                $media = $step->media()->where('file_name', Str::after($mediaName, 'storage/'))->first();
                if ($media) {
                    $media->delete();
                }

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
