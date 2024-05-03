<?php

namespace App\Repositories\LearningPath;

use App\Helpers\QueryConfig;
use App\Mail\sendSubscriptionMail;
use App\Models\Course;
use App\Models\LearningPath;
use App\Models\Quiz;
use App\Models\User;
use App\Repositories\Media\MediaRepository;
use App\Repositories\Quiz\QuizRepository;
use App\Traits\PaginationParams;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class LearningPathRepository
{

    use PaginationParams;

    public final function createLearningPath($data): LearningPath
    {
        $mediaFiles = $data['media_files'] ?? [];

        unset($data['media_files']);
        $user = auth()->user();
        $data['added_by'] = $user->id;


        $learningPath = LearningPath::create($data);
        $courses = Course::where('language', $data['language_id'])
            ->where('category', $data['category_id'])
            ->where('added_by', $user->id)
            ->where('is_public', $data['is_public'])
            ->get();
        $learningPath->courses()->attach($courses);
        if (isset($data['quiz'])) {
            QuizRepository::createQuiz($learningPath, $data['quiz']);
        }
        if (isset($mediaFiles)) {

            self::addMediaToLearningPath($learningPath->id, $mediaFiles);
        }
        return $learningPath;
    }

    /**
     * @param $learning_path_id
     * @param $files
     * @return void
     */
    private static function addMediaToLearningPath($learning_path_id, $files): void
    {
        $learning_path = LearningPath::find($learning_path_id);

        if (!$learning_path) {
            return;
        }
        foreach ($files as $file) {
            MediaRepository::attachOrUpdateMediaForModel($learning_path, $file);
        }
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
            $learningPath->delete();
        }
    }

    /**
     * @param $learningPathId
     * @return LearningPath|Collection
     */

    public static function subscribeUsersToLearningPath($learningPathId): LearningPath|Collection
    {
        $userId = auth()->user()->id;
        $learningPath = LearningPath::with(['courses' => function ($query) {
            $query->where('is_public', true);
        }])->findOrFail($learningPathId);

        $learningPathSubscriptions = [];
        $courseSubscriptions = [];


        $learningPathSubscriptions[] = [
            'learning_path_id' => $learningPathId,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        foreach ($learningPath->courses as $course) {
            $courseSubscriptions[] = [
                'course_id' => $course->id,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $user = User::find($userId);
        if ($user) {
            Mail::to($user->email)->queue(new sendSubscriptionMail(false, $learningPath->title, $learningPath->id));
        }

        // Insert learning path subscriptions
        DB::table('learning_path_subscriptions')->insert($learningPathSubscriptions);

        // Insert course subscriptions
        DB::table('course_subscription_users')->insert($courseSubscriptions);
        return $learningPath;
    }

    public static function index(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {
        $CourseQuery = Course::with('media')->with('courses')->with('quiz')->newQuery();
        LearningPath::applyFilters($queryConfig->getFilters(), $CourseQuery);
        $courses = $CourseQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())->get();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($courses, $queryConfig);
        }
        return $courses;
    }

}
