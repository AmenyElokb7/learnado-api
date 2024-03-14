<?php

namespace App\Repositories\Course;

use App\Enum\TeachingTypeEnum;
use App\Enum\UserRoleEnum;
use App\Helpers\QueryConfig;
use App\Mail\CourseAssignedMail;
use App\Mail\GoogleMeetConfirmation;
use App\Mail\sendSubscriptionMail;
use App\Models\Course;
use App\Models\Media;
use App\Models\User;
use App\Repositories\Media\MediaRepository;
use App\Traits\PaginationParams;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;


class CourseRepository
{
    use PaginationParams;

    /** add course with Media and Subscribed users and facilitator
     * @param $data
     * @return array
     * @throws Exception
     */
    public final function createCourse($data): array
    {
        $mediaFiles = $data['course_media'] ?? [];
        unset($data['course_media']);
        $data['added_by'] = Auth::user()->id;

        $course = Course::create($data);

        $selectedUserIds = $data['selectedUserIds'] ?? [];

        if (isset($data['is_public']) && !$data['is_public']) {
            self::subscribeCourseToUsers($course->id, $selectedUserIds);
        }
        if (!empty($selectedUserIds)) {
            self::subscribeCourseToUsers($course->id, $selectedUserIds);
        }


        if (isset($data['facilitator_id'])) {
            self::attachCourseToUser($course->id, $data['facilitator_id']);
        }
        if ($course->teaching_type == TeachingTypeEnum::ONLINE->value) {
            $icsContent = self::generateIcsContent($course);
            $filename = "course-{$course->id}.ics";
            Storage::disk('local')->put($filename, $icsContent);
            $pathToFile = storage_path('app/' . $filename);

            $googleMeetLink = $data['link'];
            $subscribedUsers = User::findMany($selectedUserIds)->all();

            foreach ($subscribedUsers as $user) {
                Mail::to($user->email)->send(new GoogleMeetConfirmation($course, $googleMeetLink, $pathToFile));
            }

            Storage::disk('local')->delete($filename);
        }

        $mediaNames = self::processCourseMedia($course->id, $mediaFiles);

        return [
            'course' => $course->toArray(),
            'media' => $mediaNames
        ];
    }

    /**
     * @param $course
     * @return string
     */
    private static function generateIcsContent($course): string
    {

        $startDateTime = date('Ymd\THis\Z', strtotime($course->start_time));
        $endDateTime = date('Ymd\THis\Z', strtotime($course->end_time));
        $courseCreator = $course->facilitator;
        $organizerEmail = $courseCreator->email;
        $organizerName = "{$courseCreator->first_name} {$courseCreator->last_name}";

        $icsContent = "BEGIN:VCALENDAR\r\n";
        $icsContent .= "VERSION:2.0\r\n";
        $icsContent .= "PRODID:-//Learnado//EN\r\n";
        $icsContent .= "BEGIN:VEVENT\r\n";
        $icsContent .= "UID:" . uniqid() . "\r\n";
        $icsContent .= "DTSTAMP:" . now()->format('Ymd\THis\Z') . "\r\n";
        $icsContent .= "DTSTART:{$startDateTime}\r\n";
        $icsContent .= "DTEND:{$endDateTime}\r\n";
        $icsContent .= "SUMMARY:{$course->title}\r\n";
        $icsContent .= "DESCRIPTION:{$course->description}\r\n";
        $icsContent .= "ORGANIZER;CN=\"{$organizerName}\":mailto:{$organizerEmail}\r\n";
        $icsContent .= "END:VEVENT\r\n";
        $icsContent .= "END:VCALENDAR\r\n";

        return $icsContent;
    }

    /**
     * @param $courseId
     * @param $mediaFiles
     * @return array
     */
    private static function processCourseMedia($courseId, $mediaFiles): array
    {
        if (!empty($mediaFiles)) {
            return self::addMediaToCourse($courseId, $mediaFiles);
        }
        return [];
    }

    /**
     * @param $course_id
     * @param $facilitator_id
     * @return void
     * @throws Exception
     */
    private static function attachCourseToUser($course_id, $facilitator_id): void
    {
        $course = Course::find($course_id);
        if (!$course) {
            throw new Exception(__('course_not_found'));

        }
        if ($course->facilitator_id !== $facilitator_id) {
            $course->facilitator_id = $facilitator_id;
            $course->save();
            $user = $course->facilitator;
            Mail::to($user->email)->send(new CourseAssignedMail($course->title, $user->name));
        }


    }

    /**
     * @param $courseId
     * @param array $usersIds
     * @return void
     */
    private static function subscribeCourseToUsers($courseId, array $usersIds): void
    {
        $subscriptions = [];
        foreach ($usersIds as $userId) {
            $subscriptions[] = [
                'course_id' => $courseId,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $course = Course::find($courseId);
            $user = User::find($userId);
            if ($user) {

                Mail::to($user->email)->send(new sendSubscriptionMail($courseId, $course->title));
            }
        }

        DB::table('course_subscription_users')->insert($subscriptions);
    }

    /**
     * @param $course_id
     * @return void
     * @throws Exception
     */

    public final function deleteCourse($course_id): void
    {
        $manager_id = Auth::user()->id;
        $course = Course::where('added_by', $manager_id)->find($course_id);
        if ($course) {
            $course->deleteWithRelations();
        } else {
            throw new Exception(__('course_not_found'));
        }
    }

    /**
     * @param $course_id
     * @return Model
     * @throws Exception
     */
    public final function getCourseWithMediaById($course_id): Model
    {
        $user = auth()->user();
        $query = Course::with('media')->find($course_id);
        if ($user && $user->role === UserRoleEnum::DESIGNER->value) {
            // Allow designers to see their own courses
            $query->where(function ($q) use ($user) {
                $q->where('added_by', $user->id)
                    ->orWhere(function ($q) {
                        $q->where('is_active', true)->where('is_public', true);
                    });
            });
        } else {
            // For other users or unauthenticated access, only active and public courses are available
            $query->where('is_active', true)->where('is_public', true);
        }
        return $query;

    }

    /** Designer can update his own courses
     * @param $course_id
     * @param $data
     * @param array $newMediaFiles
     * @param array $mediaToDelete
     * @param array $usersToSubscribe
     * @return Course|null
     * @throws Exception
     */
    public final function updateCourseWithMedia($course_id, $data, array $newMediaFiles = [], array $mediaToDelete = [], array $usersToSubscribe = []): Course|null
    {
        $user = Auth::user();
        $course = Course::find($course_id);
        if (!$course) {
            throw new Exception(__('course_not_found'));
        }
        if ($user->id === $course->added_by) {
            // Update course details
            $course->update($data);
            // Handle media deletion
            if (!empty($mediaToDelete)) {
                self::deleteMediaFromCourse($course_id, $mediaToDelete);
            }
            // Handle new media addition
            if (!empty($newMediaFiles)) {
                self::addMediaToCourse($course_id, $newMediaFiles);
            }

            if (!empty($usersToSubscribe)) {
                self::subscribeCourseToUsers($course_id, $usersToSubscribe);
            }
            return $course;
        } else {
            throw new Exception(__('user_not_authorized'));
        }
    }

    /**
     * @param $course_id
     * @param $files
     * @return array|null
     */
    private static function addMediaToCourse($course_id, $files): array|null
    {
        $course = Course::find($course_id);
        if (!$course) {
            return null;
        }
        $mediaItems = [];
        foreach ($files as $file) {
            $media = MediaRepository::attachOrUpdateMediaForModel($course, $file);
            $mediaItems[] = $media->file_name;
        }
        return $mediaItems;
    }

    /**
     * @param $course_id
     * @param $mediaIds
     * @return void
     */
    private static function deleteMediaFromCourse($course_id, $mediaIds): void
    {
        Media::whereIn('id', $mediaIds)
            ->where('course_id', $course_id)
            ->delete();
    }

    /** Get all validated and public courses for users and designer's own courses
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Collection
     */
    public static function index(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {
        $CourseQuery = Course::with('media')->newQuery();

        $user = Auth::user();

        $CourseQuery->where(function ($query) use ($user) {
            $query->where(function ($q) {
                $q->where('is_active', true)->where('is_public', true);
            });
        });

        // if role is designer then show only his courses
        if ($user && $user->role === UserRoleEnum::DESIGNER->value) {

            $CourseQuery->where(function ($query) use ($user) {
                $query->where('added_by', $user->id);

            });
        }
        Course::applyFilters($queryConfig->getFilters(), $CourseQuery);

        $courses = $CourseQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())->get();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($courses, $queryConfig);
        }

        return $courses;
    }

    /**
     * @throws Exception
     */
    public final function getSubscribedUsersByCourse($course_id): Collection
    {
        $user = Auth::user();
        $designer = Course::find($course_id)->added_by;
        if ($user->id !== $designer) {
            throw new Exception(__('user_not_authorized'));
        }
        $course = Course::with('subscribedUsers')->find($course_id);

        $subscribedUsers = $course->subscribedUsers;

        return
            $subscribedUsers->map(function ($user) {
                return [
                    'username' => $user->first_name . ' ' . $user->last_name,
                    'media' => $user->with('media')
                ];
            });


    }

}
