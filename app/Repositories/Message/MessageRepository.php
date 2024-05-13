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

    public static function forumMessageSend($data, $learningPathId = null, $courseId= null ) : array
    {
        $course = Course::find($courseId);
        $learningPath = LearningPath::find($learningPathId);

        if(!$course && !$learningPath) {
            throw new LengthRequiredHttpException(__('course_or_learning_path_not_found'));
        }

        $userName= Auth::user()->first_name . ' ' . Auth::user()->last_name;
        $userId = Auth::id();
        $userPicture = Auth::user()->media()->first()->file_name ?? null;
         Discussion::create([
            'discussable_id' => $courseId ?? $learningPathId,
            'discussable_type' => $course ? Course::class : LearningPath::class,
            'user_id' => $userId,
            'message' => $data
        ]);

        event(new Forum($data, $userName,$userPicture, now()->timestamp ,$courseId ?? $learningPathId));
        return ['message' => __('message_sent')];
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

        // Check if the user is subscribed or is the facilitator
        if ($discussable instanceof Course) {
            $enrolledCheck= $discussable->subscribers()->where('user_id', $authUser->id)->firstOrFail();
            $facilitatorCheck = $discussable->facilitator_id === $authUser->id;
        } else {
            $enrolledCheck=$discussable->courses()->each(function ($course) use ($authUser) {
                $course->subscribers()->where('user_id', $authUser->id)->firstOrFail();
            });
            $facilitatorCheck = $discussable->courses()->any(function ($course) use ($authUser) {
                return $course->facilitator_id === $authUser->id;
            });
        }

        if (!$facilitatorCheck && !$enrolledCheck ) {
            throw new Exception(__('user_not_authorised'));
        }

        // Fetch messages
        return $discussable->discussion()
            ->with('user.media')
            ->where('user_id', $authUser->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

}
