<?php

namespace App\Repositories\Course;

use App\Models\Course;
use App\Models\Media;
use App\Repositories\Media\MediaRepository;
use Exception;
use Illuminate\Support\Facades\Auth;


class CourseRepository
{
    /**
     * @param $data
     * @return array
     */
    public final function createCourse($data): array
    {
        $mediaFiles = $data['course_media'] ?? [];
        unset($data['course_media']);
        $data['added_by'] = Auth::user()->id;
        $course = Course::create($data);
        $mediaNames = [];
        if (!empty($mediaFiles)) {
            $mediaNames = $this->addMediaToCourse($course->id, $mediaFiles);
        }
        return [
            'course' => $course->toArray(),
            'media' => $mediaNames
        ];
    }

    /**
     * @param $course_id
     * @return void
     */

    public final function deleteCourse($course_id): void
    {
        $course = Course::find($course_id);
        $course->with('media')->delete();
    }

    /**
     * @param $course_id
     * @return Course|null
     */
    public final function getCourseWithMediaById($course_id): Course|null
    {
        return Course::with('media')->find($course_id);
    }

    /**
     * @throws Exception
     */
    public final function updateCourseWithMedia($course_id, $data, $newMediaFiles = [], $mediaToDelete = []): Course|null
    {
        $course = Course::find($course_id);
        if (!$course) {
            throw new Exception(__('messages.course_not_found'));
        }
        // Update course details
        $course->update($data);
        // Handle media deletion
        if (!empty($mediaToDelete)) {
            $this->deleteMediaFromCourse($course_id, $mediaToDelete);
        }
        // Handle new media addition
        if (!empty($newMediaFiles)) {
            $this->addMediaToCourse($course_id, $newMediaFiles);
        }
        return $course;
    }

    public final function addMediaToCourse($course_id, $files): array|null
    {
        $course = Course::find($course_id);
        if (!$course) {
            return null;
        }
        $mediaItems = [];
        foreach ($files as $file) {
            $media = MediaRepository::updateMediaFromModel($course, $file, null);
            $mediaItems[] = $media->file_name;
        }
        return $mediaItems;
    }

    public final function deleteMediaFromCourse($course_id, $mediaIds): void
    {
        Media::whereIn('id', $mediaIds)
            ->where('course_id', $course_id)
            ->delete();
    }

    /**
     * @param $course_id
     * @param $facilitator_id
     * @return Course|null
     */
    public final function attachCourseToUser($course_id, $facilitator_id): Course|null
    {
        $course = Course::find($course_id);
        if ($course) {
            $course->facilitator_id = $facilitator_id;
            $course->save();
            return $course;
        } else {
            return null;
        }
    }
}
