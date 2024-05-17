<?php
namespace App\Repositories\Message;

use App\Events\Forum;
use App\Helpers\QueryConfig;
use App\Mail\SupportMessageMail;
use App\Models\Course;
use App\Models\Discussion;
use App\Models\LearningPath;
use App\Models\SupportMessage;
use App\Events\MessageSent;
use App\Models\User;
use App\Traits\PaginationParams;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\LengthRequiredHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MessageRepository
{
    use PaginationParams;

    public final function saveMessage($userId, $data): SupportMessage
    {
        $message = SupportMessage::create([
            'user_id' => $userId,
            'message' => $data['message'],
            'subject' => $data['subject']
        ]);
        event(new MessageSent($message));
        $user = User::find($userId);
        Mail::to($user)->send(new SupportMessageMail($user, $message));
        return $message;
    }

    public static function indexAdminNotifications(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {

        $query = SupportMessage::with('user.media')->newQuery();
        SupportMessage::applyFilters($queryConfig->getFilters(), $query);
        $messages = $query->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())->get();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($messages, $queryConfig);
        }
        return $messages;
    }
    public final function MarkAsRead($messageId): void
    {
        $message = SupportMessage::find($messageId);
        if (!$message) {
            throw new LengthRequiredHttpException(__('message_not_found'));
        }
        $message->is_read = 1;
        $message->save();
    }

    /**
     * @throws Exception
     */
    public static function forumMessageSend($data, $learningPathId = null, $courseId= null ) : array
    {

        $course = Course::find($courseId);
        $learningPath = LearningPath::find($learningPathId);

        if(!$course && !$learningPath) {
           throw new Exception(__('course_or_learning_path_not_found'));
        }

       $userId = Auth::id();
        $discussion = Discussion::create([
            'discussable_id' => $course->id ?? $learningPath->id,
            'discussable_type' => $course ? Course::class : LearningPath::class,
            'user_id' => $userId,
            'message' => $data
        ]);

        event(new Forum($discussion->message, $learningPathId ?? null, $courseId ?? null));

        return ['message' => $data, 'course' => $course, 'learningPath' => $learningPath];

    }

    /**
     * @throws Exception
     */
    public static function indexForumMessages($courseId = null, $learningPathId = null): Collection
    {
        $authUser = Auth::user();

        $discussable = Course::find($courseId) ?? LearningPath::find($learningPathId);

        if (!$discussable) {
            throw new Exception(__('course_or_learning_path_not_found'));
        }
        // Check if the user is subscribed to or is the facilitator of the course/learning path
        $isEnrolledOrFacilitator = false;

        if ($discussable instanceof Course) {
            $isEnrolled = $discussable->subscribers()->where('user_id', $authUser->id)->exists();
            $isFacilitator = $discussable->facilitator_id == $authUser->id;
        } else {
            $isEnrolled = $discussable->courses()->whereHas('subscribers', function ($query) use ($authUser) {
                $query->where('user_id', $authUser->id);
            })->exists();
            $isFacilitator = $discussable->courses()->where('facilitator_id', $authUser->id)->exists();
        }
        $isEnrolledOrFacilitator = $isEnrolled || $isFacilitator;

        if (!$isEnrolledOrFacilitator) {
            throw new Exception(__('user_not_authorised'));
        }

        // Fetch all messages associated with the discussable entity
        return $discussable->discussion()
            ->with('user.media')
            ->orderBy('created_at', 'ASC')
            ->get();
    }

}
