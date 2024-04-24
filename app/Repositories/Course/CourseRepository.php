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
use Illuminate\Http\UploadedFile;
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
     * @return Course
     * @throws Exception
     */
    public final function createCourse($data): Course
    {
        $mediaFiles = $data['course_media'] ?? [];
        unset($data['course_media']);
        $data['added_by'] = Auth::user()->id;

        $course = Course::create($data);
    if (isset($data['selected_user_ids'])) {
        $selectedUserIdsString = trim($data['selected_user_ids'], "[]") ;
        $selectedUserIds = explode(',', $selectedUserIdsString);
        $selectedUserIds = array_map('trim', $selectedUserIds);
        $selectedUserIds = array_filter($selectedUserIds, 'is_numeric');
    }else {
        $selectedUserIds = [];
    }
        if (isset($data['is_public']) && !$data['is_public'])
        {
            if (!empty($selectedUserIds))
            {
            self::subscribeCourseToUsers($course->id, $selectedUserIds, true);
            }
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
        self::processCourseMedia($course->id, $mediaFiles);

        return $course;
    }

    /**
     * generate google meet link and calendar and send email to subscribed users
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
     * add media files to a course
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
     * add media files to a course
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

    /** Assign course to facilitator and send email notification
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

    /** Subscribe users to course if the course is private and send email notification
     * @param $courseId
     * @param array $userIds
     * @param bool $byAdmin
     * @return Course
     * @throws Exception
     */
    public static function subscribeCourseToUsers($courseId, array $userIds, bool $byAdmin = false): Course
    {
        $course = Course::find($courseId);
        if (!$course) {
            throw new Exception(__("course_not_found"));
        }

        if (!$byAdmin && !$course->is_public) {
            throw new Exception(__('user_not_authorized'));
        }

        $validUserIds = User::whereIn('id', $userIds)
            ->where('role', UserRoleEnum::USER->value)
            ->where('is_valid', 1)
            ->pluck('id')
            ->toArray();

        $currentSubscribers = $course->subscribers()->pluck('users.id')->toArray();
        $newSubscribers = array_diff($validUserIds, $currentSubscribers);

        foreach ($newSubscribers as $userId) {
            $user = User::find($userId);
            if ($user) {
                Mail::to($user->email)->queue(new sendSubscriptionMail(true, $course->title, $courseId));
            }
        }

        $course->subscribers()->sync($validUserIds);
        return $course;
    }
    /** delete the course and its relations
     * @param $course_id
     * @return void
     * @throws Exception
     */

    /**
     * Delete a course and all its relations.
     *
     * @param int $course_id The ID of the course to delete.
     * @return void
     * @throws Exception
     */
    public final function deleteCourse(int $course_id): void
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
     * @param $mediaIds
     * @return void
     */
    private static function deleteMediaFromCourse($course_id, $mediaIds): void
    {
        Media::whereIn('id', $mediaIds)
            ->where('course_id', $course_id)
            ->delete();
    }

    /** Designer can update his own courses
     * @param $course_id
     * @param $data
     * @return Course|null
     * @throws Exception
     */
    public final function updateCourse($course_id, $data): Course|null
    {
        DB::beginTransaction();
        try{
            $course = Course::find($course_id);
            if (!$course) {
                throw new Exception(__('course_not_found'));
            }
            if (Auth::id() != $course->added_by) {
                throw new Exception(__('user_not_authorized'));
            }
            // Update course details
            $course->update($data);
                // Handle update course media
                if (isset($data['course_media']) && $data['course_media'] instanceof UploadedFile) {
                    $currentMedia = $course->media()->first();
                    MediaRepository::attachOrUpdateMediaForModel($course, $data['course_media'], $currentMedia ? $currentMedia->id : null);
                }
                if (isset($data['selected_user_ids'])) {
                    $selectedUserIdsString = trim($data['selected_user_ids'], "[]") ;
                    $selectedUserIds = explode(',', $selectedUserIdsString);
                    $selectedUserIds = array_map('trim', $selectedUserIds);
                    $selectedUserIds = array_filter($selectedUserIds, 'is_numeric');
                }else {
                    $selectedUserIds = [];
                }
                if (!empty($selectedUserIds))
                {
                    self::subscribeCourseToUsers($course->id, $selectedUserIds, true);
                }

                DB::commit();
                return $course;
            }
            catch (Exception $e) {
            DB::rollBack();
            throw $e;}
    }

    /** Get all validated and public courses for users and designer's own courses
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Collection
     */
    public static function index(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {
        $CourseQuery = Course::with([
            'media',
            'steps',
            'steps.media',
            'steps.quiz',
            'steps.quiz.questions',
            'steps.quiz.questions.answers',
            'subscribers',
            'facilitator' => function ($query) {
                $query->with('media:model_id,file_name')->select('id', 'first_name', 'last_name', 'email');
            },
            'language'
        ])
            ->selectRaw('courses.*, (courses.price - (courses.price * courses.discount / 100)) as final_price')
            ->newQuery();
        Course::applyFilters($queryConfig->getFilters(), $CourseQuery);
        $courses = $CourseQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())->get();
        $courses->each(function ($course) {
            $course->lessons_count = $course->steps->count();

            $course->duration = $course->steps->sum('duration');
        });
        $courses->each(function ($course) {
            $course->subscribed_users_count = $course->subscribers->count();
        });
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($courses, $queryConfig);
        }
        return $courses;
    }

    /**
     * Fetch a course by ID, optionally applying filters.
     *
     * @param int $courseId The ID of the course.
     * @param QueryConfig|null $queryConfig Optional filters and settings for the query.
     * @return Model The course model instance.
     * @throws Exception
     */
    public final function getCourseById(int $courseId, ?QueryConfig $queryConfig = null): Model
    {
        $query = Course::with([
            'media',
            'steps',
            'steps.media',
            'steps.quiz',
            'steps.quiz.questions',
            'steps.quiz.questions.answers',
            'subscribers',
            'facilitator' => function ($query) {
                $query->with('media:model_id,file_name')->select('id', 'first_name', 'last_name', 'email');
            },
            'language'
        ])
            ->selectRaw('courses.*, (courses.price - (courses.price * courses.discount / 100)) as final_price')
            ->newQuery();


        $user = auth()->user();
        if ($user && $user->role == UserRoleEnum::USER) {
            $query->with("subscribers")->newQuery();
        }

        if ($queryConfig) {
            Course::applyFilters($queryConfig->getFilters(), $query);
        }
        $course = $query->find($courseId);

        $course?->each(function ($course) {
            $course->lessons_count = $course->steps->count();
            $course->duration = $course->steps->sum('duration');
        });

        $course->each(function ($course) {
            $course->subscribed_users_count = $course->subscribers->count();
        });

        return $query->find($courseId);
    }

}
