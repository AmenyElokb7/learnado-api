<?php

namespace App\Repositories\Course;

use App\Enum\TeachingTypeEnum;
use App\Enum\UserRoleEnum;
use App\Helpers\QueryConfig;
use App\Mail\CourseAssignedMail;
use App\Mail\GoogleMeetConfirmation;
use App\Mail\SendCertificateMail;
use App\Mail\sendSubscriptionMail;
use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\Media;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Repositories\Media\MediaRepository;
use App\Traits\PaginationParams;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $mediaFile = $data['course_media'] ?? "";
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
        // check the media files
        MediaRepository::attachOrUpdateMediaForModel($course, $mediaFile,null);

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
    public static function subscribeCourseToUsers($courseId, array $userIds= [], bool $byAdmin = false): Course
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
        $authUserId= Auth::id();
        $subscribedUserCourse= Course::whereHas('subscribers', function ($query) use ($authUserId) {
                $query->where('users.id', $authUserId);
        })->get();
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

        $courses = $CourseQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection());
        if($authUserId){
            // select the courses that the user not enrolled in
            $courses->whereNotIn('id', $subscribedUserCourse->pluck('id'));
        }
        $courses = $courses->get();
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
        $user = auth()->user();

        $query = Course::with([
            'media',
            'steps' => function ($query) use ($user) {
                $query->with(['media', 'quiz' => function ($query) use ($user) {
                    $query->with(['questions.answers']);
                    if ($user) {
                        $query->with(['latestAttempt' => function ($q) use ($user) {
                            $q->where('user_id', $user->id)->latest();

                        }]);
                    }
                }]);
            },
            'subscribers',
            'facilitator' => function ($query) {
                $query->with('media:model_id,file_name')->select('id', 'first_name', 'last_name', 'email');
            },
            'language'
        ])
            ->selectRaw('courses.*, (courses.price - (courses.price * courses.discount / 100)) as final_price')
            ->newQuery();

        $user = auth()->user();
        if ($user && $user->role == UserRoleEnum::USER->value) {
            $query->with("subscribers")->newQuery();
        }

        $course = $query->find($courseId);

        if (!$course) {
            throw new Exception(__('course_not_found'));
        }
        // if course has steps else lessons_count = 0
        $course->lessons_count = $course->steps->count() ?:0;
        $course->duration = $course->steps->sum('duration') ?:0;
        $course->subscribed_users_count = $course->subscribers->count() ?:0;

        if (Auth::id()) {
            $user = Auth::user();
            $course->is_subscribed = $course->subscribers->contains('id', $user->id);

            // Check if the user is subscribed to the course
            $subscriber = $course->subscribers->find($user->id);
            if ($subscriber) {
                // check if the course in the table course_subscription users is completed
                $course->is_completed= $course->subscribers()->wherePivot('user_id', $user->id)->wherePivot('is_completed', 1)->exists();
            } else {
                // Handle the case where the user is not subscribed
                $course->is_completed = false;
            }
            foreach ($course->steps as $step) {
                if ($step->quiz && $step->quiz->latestAttempt) {
                    $lastAttempt = $step->quiz->latestAttempt;

                    if ($lastAttempt) {

                        $cooldownPeriod = 120; // 2 hours
                        $created_at= $lastAttempt->created_at;
                        $nextAttemptTime = $lastAttempt->created_at->addMinutes($cooldownPeriod);

                        if (now()->lessThan($nextAttemptTime)) {
                            $timeLeft = now()->diffInSeconds($nextAttemptTime, false);
                            $step->quiz->time_left = $timeLeft > 0 ? $timeLeft : 0;
                        } else {
                            $step->quiz->time_left = 0;
                        }
                    } else {
                        $step->quiz->time_left = 0;
                    }
                }
            }
        }
        return $course;
    }

    /**
     * Fetch courses for enrolled users
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Collection
     */
    public static function indexCoursesForEnrolledUsers(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {
        $authUserId = Auth::id();

        $CourseQuery = Course::with([
            'media',
            'steps.media',
            'steps.quiz.questions.answers',
            'subscribers',
            'facilitator' => function ($query) {
                $query->with('media:model_id,file_name')->select('id', 'first_name', 'last_name', 'email');
            },
            'language'
        ])
            ->selectRaw('courses.*, (courses.price - (courses.price * courses.discount / 100)) as final_price');

        // Apply filters to the query
        Course::applyFilters($queryConfig->getFilters(), $CourseQuery);

        // If authenticated, filter based on subscription status
        if ($authUserId) {
            $CourseQuery->whereHas('subscribers', function ($query) use ($authUserId) {
                $query->where('users.id', $authUserId);
            });
        // get the is_subscribed attribute and return it in the response
            $CourseQuery->addSelect([
                DB::raw("CASE WHEN EXISTS (SELECT * FROM course_subscription_users WHERE course_subscription_users.course_id = courses.id AND course_subscription_users.user_id = $authUserId) THEN 1 ELSE 0 END as is_subscribed")
            ]);

        }
        // Apply ordering
        $CourseQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection());

        // Decide whether to get a paginated result or a collection
        $courses = $queryConfig->getPaginated()
            ? $CourseQuery->paginate($queryConfig->getPerPage())
            : $CourseQuery->get();

        $courses->each(function ($course) {
            $course->lessons_count = $course->steps->count();
            $course->duration = $course->steps->sum('duration');
            $course->subscribed_users_count = $course->subscribers->count();

        });
        return $courses;
    }

    /** when user completes a course he will get a certificate
    * @param $course_id
    * @return void
     * @throws Exception
     */

    public final function completeCourse($course_id) : void
    {
        $user_id = Auth::id();
        $user = Auth::user();
        $course = Course::findOrFail($course_id);

        if (!$course){
            throw new NotFoundHttpException(__('course_not_found'));
        }
        // check if the course is already completed
        if ($course->subscribers()->wherePivot('user_id', $user_id)->wherePivot('is_completed', 1)->exists()) {
            throw new Exception(__('course_already_completed'));
        }
        // update the course_subscription table and set is_completed to 1
        $course->subscribers()->updateExistingPivot($user_id, ['is_completed' => 1]);
        $pdfPath = $this->generatePdfCertificate($course_id, $user_id);
        Mail::to($user->email)->send(new SendCertificateMail($pdfPath, $user));
    }

    /** generate certificate for a completed course
    * @param $course_id
    * @param $user_id
    * @return string
    */
    private function generatePdfCertificate($course_id, $user_id): string
    {
        $course = Course::findOrFail($course_id);
        $user = User::findOrFail($user_id);

        $data = [
            'title' => $course->title,
            'user_name' => $user->first_name . ' ' . $user->last_name,
            'message' => "Congratulations on completing the course!"
        ];

        $pdf = Pdf::loadView('certificates.template', $data);
        $pdfPath = 'certificates/' . uniqid() . '.pdf';
        $pdf->save(storage_path('app/public/' . $pdfPath));

        $course->certificates()->create([
            'user_id' => $user_id,
            'certificate_path' => $pdfPath
        ]);
        return $pdfPath;
    }

    /** get all certificates for a user
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Collection
     */
    public final function indexCourseCertificates(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {
        $authUserId = Auth::id();

        $certificateQuery = CourseCertificate::with(['course'])
            ->where('user_id', $authUserId);
        CourseCertificate::applyFilters($queryConfig->getFilters(), $certificateQuery);
        $certificateQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection());

        $certificates = $queryConfig->getPaginated()
            ? $certificateQuery->paginate($queryConfig->getPerPage())
            : $certificateQuery->get();

        // Append the download path for each certificate
        $certificates->transform(function ($certificate) {
            $certificate->download_url = route('certificates.download', $certificate->id);
            return $certificate;
        });

        return $certificates;
    }
    /**
     * @throws Exception
     */
    public final function getCertificateFilePath($certificateId): string
    {
        $certificate = CourseCertificate::findOrFail($certificateId);
        $filePath = storage_path('app/public/' . $certificate->certificate_path);
        if (!file_exists($filePath)) {
            throw new \Exception('File not found.');
        }
        return $filePath;
    }

    /**
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Collection
     */

    public final function indexCompletedCourses(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {
        $authUserId = Auth::id();

        $completedCoursesQuery = Course::with([
            'media',
            'steps.media',
            'steps.quiz.questions.answers',
            'subscribers',
            'facilitator' => function ($query) {
                $query->with('media:model_id,file_name')->select('id', 'first_name', 'last_name', 'email');
            },
            'language'
        ])
            ->selectRaw('courses.*, (courses.price - (courses.price * courses.discount / 100)) as final_price');

        // Apply filters to the query
        Course::applyFilters($queryConfig->getFilters(), $completedCoursesQuery);

        // If authenticated, filter based on subscription status
        if ($authUserId) {
            // Get only completed courses from the course_subscription table
            $completedCoursesQuery = $completedCoursesQuery->whereHas('subscribers', function ($query) use ($authUserId) {
                $query->where('users.id', $authUserId)
                    ->where('course_subscription_users.is_completed', 1);
            });
        }


        // Apply ordering
        $completedCoursesQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection());

        // Decide whether to get a paginated result or a collection
        return $queryConfig->getPaginated()
            ? $completedCoursesQuery->paginate($queryConfig->getPerPage())
            : $completedCoursesQuery->get();
    }

    /** get all courses in the cart
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Collection
     */
    public static function indexCartCourses(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {
        $authUserId = Auth::id();
        $cartCoursesQuery = Course::with([
            'media',
            'facilitator' => function ($query) {
                $query->with('media:model_id,file_name')->select('id', 'first_name', 'last_name', 'email');
            },

        ])
            ->selectRaw('courses.*, (courses.price - (courses.price * courses.discount / 100)) as final_price, cart.id as cart_id')
            ->join('cart', 'courses.id', '=', 'cart.course_id')
            ->where('cart.user_id', '=', $authUserId)
            ->newQuery();

        // Apply filters to the query
        Course::applyFilters($queryConfig->getFilters(), $cartCoursesQuery);

        // If authenticated, filter based on subscription status
        if ($authUserId) {
            // Get only completed courses from the course_subscription table
            $cartCoursesQuery = $cartCoursesQuery->whereHas('usersInCart', function ($query) use ($authUserId) {
                $query->where('users.id', $authUserId);
            });
        }

        // Apply ordering
        $cartCoursesQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection());

        // Decide whether to get a paginated result or a collection
        $courses= $queryConfig->getPaginated()
            ? $cartCoursesQuery->paginate($queryConfig->getPerPage())
            : $cartCoursesQuery->get();
        return $courses->map(function ($course) {
            return [

                    'course' => $course->toArray(),
                    'cart_id' => $course->cart_id,  // Assuming 'cart_id' was correctly selected and available

            ];
        });

    }

    /** add course to cart
     * @throws Exception
     * @param $course_id
     */
    public static function addToCart($course_id): void
    {
        $authUserId = Auth::id();
        $course = Course::findOrFail($course_id);
        if (!$course) {
            throw new Exception(__('course_not_found'));
        }
        $course->usersInCart()->attach($authUserId);
    }

}


