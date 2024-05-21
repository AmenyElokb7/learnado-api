<?php

namespace App\Repositories\LearningPath;

use App\Helpers\QueryConfig;
use App\Mail\sendSubscriptionMail;
use App\Models\Attestation;
use App\Models\Course;
use App\Models\LearningPath;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Repositories\Media\MediaRepository;
use App\Repositories\Quiz\QuizRepository;
use App\Traits\PaginationParams;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class LearningPathRepository
{

    use PaginationParams;

    /**
     * @throws Exception
     */
    public final function createLearningPath($data): LearningPath
    {
        $mediaFile = $data['media_file'] ?? null;
        unset($data['media_file']);
        $user = auth()->user();
        $data['added_by'] = $user->id;
        $learningPath = LearningPath::create($data);
        $courses = self::filterCourses($data);
        $allSubscribers = collect();
        foreach ($courses as $course) {
            $learningPath->courses()->attach($course);
            $allSubscribers = $allSubscribers->merge($course->subscribers);
        }
        $learningPath->save();
        if (isset($data['quiz'])) {
            QuizRepository::createQuiz($learningPath, $data['quiz'], true);
        }
        if ($mediaFile) {
            self::addMediaToLearningPath($learningPath->id, $mediaFile);
        }
        if (!$data['is_public']){
            $additionalUserIds = $data['additional_user_ids'] ?? [];
            $allSubscribers = $allSubscribers->merge(User::whereIn('id', $additionalUserIds)->get())->unique('id');
            $subscriberIds = $allSubscribers->pluck('id')->toArray();
            self::subscribeUsersToLearningPath($learningPath->id, $subscriberIds, true);
        }
        return $learningPath;
    }

    /**
     * function to get the courses for each learning path
     */
    public final function filterCourses($data) : Collection {
        $user= Auth::user();
        return Course::query()->where('language_id', $data['language_id'])
            ->where('category_id', $data['category_id'])
            ->where('added_by', $user->id)
            ->where('is_public', $data['is_public'])
            ->where('is_active', true)
            ->where('is_offline', false)
            ->get();
    }
    /**
     * @param $learning_path_id
     * @param $file
     * @return void
     */
    private static function addMediaToLearningPath($learning_path_id, $file): void
    {
        $learning_path = LearningPath::find($learning_path_id);
        if (!$learning_path) {
            return;
        }
        MediaRepository::attachOrUpdateMediaForModel($learning_path, $file);
    }

    /**
     * @param $learningPathId
     * @param $data
     * @return LearningPath
     * @throws Exception
     */
    public final function updateLearningPath($learningPathId, $data): LearningPath
    {
        $user = auth()->user();
        $learningPath = LearningPath::where('added_by', $user->id)->findOrFail($learningPathId);
        if (!$learningPath) {
            throw new Exception(__('user_not_authorized'), ResponseAlias::HTTP_FORBIDDEN);
        }
        $files = $data['media_files'] ?? null;
        $mediaToRemove = $data['media_to_remove'] ?? null;
        $this->updateMedia($learningPath, $files, $mediaToRemove);

        $learningPath->fill($data)->save();
        return $learningPath;
    }

    /**
     * @param $learning_path
     * @param $files
     * @param $mediaToRemove
     * @return void
     */
    private function updateMedia($learning_path, $files, $mediaToRemove): void
    {
        if (!empty($mediaToRemove)) {
            foreach ($mediaToRemove as $mediaId) {
                MediaRepository::detachMediaFromModel($learning_path, $mediaId);
            }
        }
        if (!empty($files)) {
            foreach ($files as $file) {
                MediaRepository::attachOrUpdateMediaForModel($learning_path, $file, null, $file->getClientOriginalName());
            }
        }
    }

    /**
     * @throws Exception
     */
    public final function updateLearningPathQuiz($learningPathId, $quizData): Quiz
    {
        $learningPath = LearningPath::findOrFail($learningPathId);
        return QuizRepository::updateQuiz($learningPath, $quizData, true);
    }


    /**
     * @param $learningPathId
     * @return void
     * @throws Exception
     */
    public final function deleteQuizFromLearningPath($learningPathId): void
    {
        $learningPath = LearningPath::find($learningPathId);
        if ($learningPath) {
            QuizRepository::deleteQuiz($learningPath);
        }
    }

    public final function deleteLearningPath($learningPathId): void
    {
        $learningPath = LearningPath::find($learningPathId);
        if ($learningPath) {
            $learningPath->delteWithRelations();
        }
    }

    /**
     * @param $learningPathId
     * @param array $userIds
     * @param bool $byAdmin
     * @return Builder|\Illuminate\Database\Eloquent\Collection|Model|Builder[]
     * @throws Exception
     */

    public static function subscribeUsersToLearningPath($learningPathId, array $userIds, bool $byAdmin = false): Builder|array|\Illuminate\Database\Eloquent\Collection|Model
    {
        $learningPath = LearningPath::with(['courses'])->findOrFail($learningPathId);

        if (!$byAdmin && !$learningPath->is_public) {
            throw new Exception(__('user_not_authorized'));
        }
        $validUserIds = User::whereIn('id', $userIds)
            ->where('is_valid', true)
            ->whereDoesntHave('subscribedLearningPaths', function ($query) use ($learningPathId) {
                $query->where('learning_path_id', $learningPathId);
            })
            ->whereDoesntHave('subscribedCourses', function ($query) use ($learningPath) {
                $query->whereIn('course_id', $learningPath->courses->pluck('id')->toArray());
            })
            ->pluck('id')
            ->toArray();
        $learningPathSubscriptions = collect(
            array_map(
                function ($userId) use ($learningPathId) {
                    return [
                        'learning_path_id' => $learningPathId,
                        'user_id' => $userId,
                        'created_at' => now()->timestamp,
                        'updated_at' => now()->timestamp,
                    ];
                },
                $validUserIds
            )
        );
        $courseSubscriptions = [];
        foreach ($learningPath->courses as $course) {
            foreach ($validUserIds as $userId) {
                $courseSubscriptions[] = [
                    'course_id' => $course->id,
                    'user_id' => $userId,
                    'created_at' => now()->timestamp,
                    'updated_at' => now()->timestamp(),
                ];
            }
        }
        DB::beginTransaction();
        try {
            DB::table('learning_path_subscriptions')->insert($learningPathSubscriptions->toArray());
            DB::table('course_subscription_users')->insert($courseSubscriptions);
            foreach ($validUserIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    Mail::to($user->email)->queue(new sendSubscriptionMail(false, $learningPath->title, $learningPath->id));
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $learningPath;
    }
    /**
     * index learning paths
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Collection
     */

    public static function index(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {
        $authUserId = Auth::id();
        $subscribedUserCourseIds = Course::whereHas('subscribers', function ($query) use ($authUserId) {
            $query->where('users.id', $authUserId);
        })->pluck('id');
        $LearningPathQuery = LearningPath::with([
            'media',
            'courses',
            'quiz',
            'quiz.questions',
            'quiz.questions.answers',
            'category',
            'language',
            // courses with their extended relations
            'courses' => function ($query) use ($subscribedUserCourseIds, $authUserId) {
                $query->with([
                    'media',
                    'steps.media',
                    'steps.quiz.questions.answers',
                    'subscribers',
                    'facilitator' => function ($query) {
                        $query->with('media:model_id,file_name')->select('id', 'first_name', 'last_name', 'email');
                    },
                    'language',
                    'category'
                ])->selectRaw('courses.*, (courses.price - (courses.price * courses.discount / 100)) as final_price')
                    // Here specify the table name with the id to remove ambiguity
                    ->whereNotIn('courses.id', $subscribedUserCourseIds);
            }
        ])->withCount(['courses', 'subscribedUsersLearningPath as subscribed_users_count'])
            ->newQuery();
        LearningPath::applyFilters($queryConfig->getFilters(), $LearningPathQuery);
        $learningPaths = $LearningPathQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection());
        $subscribedUserLearningPath= LearningPath::whereHas('subscribedUsersLearningPath', function ($query) use ($authUserId) {
            $query->where('users.id', $authUserId);
        })->get();

        if($authUserId){
            $learningPaths->whereNotIn('id', $subscribedUserLearningPath->pluck('id'));
            $learningPaths->addSelect([
                DB::raw("CASE WHEN EXISTS (SELECT * FROM learning_path_subscriptions WHERE learning_path_subscriptions.learning_path_id = learning_paths.id AND learning_path_subscriptions.user_id = $authUserId) THEN 1 ELSE 0 END as is_subscribed")
            ]);
        }
        $learningPaths=$learningPaths->get();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($learningPaths, $queryConfig);
        }
        return $learningPaths;
    }
    public static function setLearningPathActive($learningPathId): void
    {
        $learningPath = LearningPath::find($learningPathId);
        if ($learningPath) {
            $learningPath->is_active = true;
            $learningPath->save();
        }
    }
    public static function setLearningPathOffline($learningPathId): void
    {
        $learningPath = LearningPath::find($learningPathId);
        if ($learningPath) {
            $learningPath->is_offline = true;
            $learningPath->save();
        }
    }
    public static function setLearningPathOnline($learningPathId): void
    {
        $learningPath = LearningPath::find($learningPathId);
        if ($learningPath) {
            $learningPath->is_offline = false;
            $learningPath->save();
        }
    }
    /**
     * @throws Exception
     */
    public static function completeLearningPath($learningPathId): void
    {
        $learningPath = LearningPath::find($learningPathId);
        if (!$learningPath) {
            throw new Exception(__('learning_path_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
        $learningPath->subscribedUsersLearningPath()->updateExistingPivot(auth()->user()->id, ['is_completed' => true]);
    }
    public static function indexEnrolledLearningPaths(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {
        $authUserId = Auth::id();
        $subscribedUserCourseIds = Course::whereHas('subscribers', function ($query) use ($authUserId) {
            $query->where('users.id', $authUserId);
        })->pluck('id');
        $LearningPathQuery = LearningPath::with([
            'media',
            'courses',
            'quiz',
            'quiz',
            'quiz.questions',
            'quiz.questions.answers',
            'category',
            'language',
            'courses' => function ($query) use ($subscribedUserCourseIds, $authUserId) {
                $query->with([
                    'media',
                    'steps.media',
                    'steps.quiz.questions.answers',
                    'subscribers',
                    'facilitator' => function ($query) {
                        $query->with('media:model_id,file_name')->select('id', 'first_name', 'last_name', 'email');
                    },
                    'language',
                    'category'
                ])->selectRaw('courses.*, (courses.price - (courses.price * courses.discount / 100)) as final_price')
                    ->whereNotIn('courses.id', $subscribedUserCourseIds);
            }
        ])->withCount(['courses', 'subscribedUsersLearningPath as subscribed_users_count'])
            ->newQuery();

        LearningPath::applyFilters($queryConfig->getFilters(), $LearningPathQuery);

        $LearningPathQuery->addSelect([
            DB::raw("CASE WHEN EXISTS (SELECT * FROM learning_path_subscriptions WHERE learning_path_subscriptions.learning_path_id = learning_paths.id AND learning_path_subscriptions.user_id = $authUserId) THEN 1 ELSE 0 END as is_subscribed")
        ]);

        $learningPaths = $LearningPathQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())
            ->whereHas('subscribedUsersLearningPath', function ($query) use ($authUserId) {
                $query->where('users.id', $authUserId);
            })->get();

        if ($queryConfig->getPaginated()) {
            return self::applyPagination($learningPaths, $queryConfig);
        }
        return $learningPaths;
    }

    /**
     * index the completed learning paths for user
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Collection
     */
    public static function indexCompletedLearningsPathsForUser(QueryConfig $queryConfig) : LengthAwarePaginator | Collection
    {
        $authUserId = Auth::id();
        $subscribedUserCourseIds = Course::whereHas('subscribers', function ($query) use ($authUserId) {
            $query->where('users.id', $authUserId);
        })->pluck('id');
        $LearningPathQuery = LearningPath::with([
            'media',
            'courses',
            'quiz',
            'quiz',
            'quiz.questions',
            'quiz.questions.answers',
            'category',
            'language',
            'courses' => function ($query) use ($subscribedUserCourseIds, $authUserId) {
                $query->with([
                    'media',
                    'steps.media',
                    'steps.quiz.questions.answers',
                    'subscribers',
                    'facilitator' => function ($query) {
                        $query->with('media:model_id,file_name')->select('id', 'first_name', 'last_name', 'email');
                    },
                    'language',
                    'category'
                ])->selectRaw('courses.*, (courses.price - (courses.price * courses.discount / 100)) as final_price')
                    ->whereNotIn('courses.id', $subscribedUserCourseIds);
            }
        ])->withCount(['courses', 'subscribedUsersLearningPath as subscribed_users_count'])
            ->newQuery();

        LearningPath::applyFilters($queryConfig->getFilters(), $LearningPathQuery);

        $LearningPathQuery->addSelect([
            DB::raw("CASE WHEN EXISTS (SELECT * FROM learning_path_subscriptions WHERE learning_path_subscriptions.learning_path_id = learning_paths.id AND learning_path_subscriptions.user_id = $authUserId) THEN 1 ELSE 0 END as is_subscribed")
        ]);

        $learningPaths = $LearningPathQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())
            ->whereHas('subscribedUsersLearningPath', function ($query) use ($authUserId) {
                $query->where('users.id', $authUserId)
                    ->where('learning_path_subscriptions.is_completed', 1);
            })->get();

        if ($queryConfig->getPaginated()) {
            return self::applyPagination($learningPaths, $queryConfig);
        }
        return $learningPaths;

    }

    /**
     * add learning path to cart
     * @throws Exception
     * @param $learningPathId
     * @return void
     */
    public static function addToCart($learningPathId): void
    {
        $learningPath = LearningPath::find($learningPathId);
        if (!$learningPath) {
            throw new Exception(__('learning_path_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
        if($learningPath->usersInCart->contains(auth()->id())){
            throw new Exception(__('learning_path_already_in_cart'), ResponseAlias::HTTP_BAD_REQUEST);
        }
        $learningPath->usersInCart()->attach(auth()->id());
    }

    /**
     * get learning path by id
     * @param $learningPathId
     * @param QueryConfig|null $queryConfig
     * @return Model
     * @throws Exception
     */
    public static function getLearningPathById($learningPathId, ?QueryConfig $queryConfig = null) : Model
    {
        $user = auth()->user();
        $authUserId = $user->id ?? null;
        $subscribedUserCourseIds = Course::whereHas('subscribers', function ($query) use ($authUserId) {
            $query->where('users.id', $authUserId);
        })->pluck('id');
        $query = LearningPath::with([
            'media',
            'quiz',
            'quiz',
            'quiz.questions',
            'quiz.questions.answers',
            'category',
            'language',
            // courses with their extended relations
            'courses' => function ($query) use ($authUserId) {
                $query->with([
                    'media',
                    'steps.media',
                    'steps.quiz.questions.answers',
                    'subscribers',
                    'facilitator' => function ($query) {
                        $query->with('media:model_id,file_name')->select('id', 'first_name', 'last_name', 'email');
                    },
                    'language',
                    'category'
                ])->selectRaw('courses.*, (courses.price - (courses.price * courses.discount / 100)) as final_price');
            }
        ])->withCount(['courses', 'subscribedUsersLearningPath as subscribed_users_count'])
        ->newQuery();
        $query->with(['quiz' => function ($query) use ($authUserId) {
            $query->withCount(['latestAttempt as has_attempt' => function ($query) use ($authUserId) {
                $query->where('user_id', $authUserId);
            }])->with(['latestAttempt' => function ($query) use ($authUserId) {
                $query->select('id', 'quiz_id', 'user_id', 'passed', 'needs_review', 'created_at')
                    ->where('user_id', $authUserId)
                    ->latest('created_at')
                    ->first();
            }])
                ->addSelect([
                    'status' => QuizAttempt::selectRaw("
            CASE
                WHEN passed = 1 THEN 'success'
                WHEN passed = 0 AND needs_review = 0 THEN 'fail'
                WHEN needs_review = 1 THEN 'pending'
                ELSE 'no_attempt'
            END")
                        ->whereColumn('quiz_id', 'quizzes.id')
                        ->where('user_id', $authUserId)
                        ->latest('created_at')
                        ->limit(1),
                ]);
        }]);
        $learningPath = $query->find($learningPathId);

        if ($learningPath->courses) {
            foreach ($learningPath->courses as $course) {
                $course->lessons_count = $course->steps->count() ?: 0;
                $course->duration = $course->steps->sum('duration') ?: 0;
                $course->subscribed_users_count = $course->subscribers->count() ?: 0;
                $course->is_subscribed = $course->subscribers->contains('id', $authUserId);
                $course->is_completed = $course->subscribers->find($authUserId)->pivot->is_completed ?? false;
            }
        }
        $learningPath->has_quiz = $learningPath->quiz ? 1 : 0;
        if (!$learningPath) {
            throw new Exception(__('learning_path_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
        if(Auth::id()){
            $learningPath->is_subscribed = $learningPath->subscribedUsersLearningPath->contains('id', $user->id);
            $subscriber = $learningPath->subscribedUsersLearningPath->find($user->id);
            if($subscriber){
                $learningPath->is_completed = $subscriber->pivot->is_completed;
            }else {
                $learningPath->is_completed = false;
            }
            $quiz = $learningPath->quiz;
        }
        return $learningPath;
    }

    /**
     * index learning path attestations for users
     * @param QueryConfig $paginationParams
     * @return LengthAwarePaginator|Collection
     */
    public static function indexLearningPathAttestationsForUsers(QueryConfig $paginationParams): LengthAwarePaginator|Collection
    {
        $user = Auth::user();
        $attestationQuery = Attestation::where('user_id', $user->id)
            ->with('learningPath')->newQuery();
        $attestations = $attestationQuery->orderBy($paginationParams->getOrderBy(), $paginationParams->getDirection());
        if ($paginationParams->getPaginated()) {
            return $attestations->paginate($paginationParams->getPerPage());
        } else {
            return $attestations->get();
        }
    }
}
