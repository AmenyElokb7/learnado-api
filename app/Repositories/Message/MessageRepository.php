<?php
namespace App\Repositories\Message;

use App\Events\Forum;
use App\Events\PrivateMessage;
use App\Helpers\QueryConfig;
use App\Mail\SupportMessageMail;
use App\Models\Course;
use App\Models\Discussion;
use App\Models\LearningPath;
use App\Models\Message;
use App\Models\SupportMessage;
use App\Events\MessageSent;
use App\Models\User;
use App\Traits\PaginationParams;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\LengthRequiredHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MessageRepository
{
    use PaginationParams;

    /** send message to support
     * @param $userId
     * @param $data
     * @return SupportMessage
     */
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

    /**
     * index all messages for admin
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Collection
     */

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

    /**
     * send message in a forum
     * @param $data
     * @param null $learningPathId
     * @param null $courseId
     * @return array
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
     * index all messages for user in the forum
     * @throws Exception
     * @param $courseId
     * @param $learningPathId
     * @return Collection
     */
    public static function indexForumMessages($courseId = null, $learningPathId = null): Collection
    {
        $authUser = Auth::user();
        $discussable = null;
        $discussable = LearningPath::find($learningPathId);
        if (!$discussable) {
            $discussable = Course::find($courseId);
        }
        if (!$discussable) {
            throw new Exception(__('course_or_learning_path_not_found'));
        }
        $isEnrolledOrFacilitator = false;
        if ($discussable instanceof Course) {
            $isEnrolled = $discussable->subscribers()->where('user_id', $authUser->id)->exists();
            $isFacilitator = $discussable->facilitator_id == $authUser->id;
        } else if ($discussable instanceof LearningPath) {
            $isEnrolled = $discussable->courses()->whereHas('subscribers', function ($query) use ($authUser) {
                $query->where('user_id', $authUser->id);
            })->exists();
            $isFacilitator = $discussable->courses()->where('facilitator_id', $authUser->id)->exists();
        }
        $isEnrolledOrFacilitator = $isEnrolled || $isFacilitator;
        if (!$isEnrolledOrFacilitator) {
            throw new Exception(__('user_not_authorised'));
        }
        return $discussable->discussion()
            ->with('user.media')
            ->orderBy('created_at', 'ASC')
            ->get();
    }

    /**
     * send private message
     * @param $data
     * @param $receiverId
     * @return Message
     */

    public static function sendPrivateMessage($data, $receiverId): Message
    {
        Log::info('Sending private message', [
            'data' => $data,
            'receiverId' => $receiverId
        ]);
        $message = Message::create([
            'message' => $data,
        ]);
        DB::table('user_messages')->insert([
            'message_id' => $message->id,
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId
        ]);
        event(new PrivateMessage($message, $receiverId, Auth::id()));
        return $message;
    }

    /**
     * index all private messages for user
     * @return Collection
     */
    public static function indexPrivateMessages() : Collection
    {
        $authUser = Auth::user();
        return DB::table('messages')
            ->join('user_messages as um', 'messages.id', '=', 'um.message_id')
            ->join('users as receiver', 'um.receiver_id', '=', 'receiver.id')
            ->join('users as sender', 'um.sender_id', '=', 'sender.id')
            ->leftJoin('media', function ($join) {
                $join->on('sender.id', '=', 'media.model_id')
                    ->where('media.model_type', User::class);
            })
            ->select('messages.id as id', 'messages.message as text', 'um.receiver_id','um.sender_id', 'messages.created_at as timestamp', 'receiver.first_name', 'receiver.last_name', 'media.file_name as avatar')
            ->addSelect(DB::raw('(SELECT message FROM messages WHERE id = (SELECT MAX(id) FROM messages WHERE id = um.message_id)) as last_message '))
            ->addSelect(DB::raw('(SELECT created_at FROM messages WHERE id = (SELECT MAX(id) FROM messages WHERE id = um.message_id)) as last_message_timestamp '))
            ->where('um.sender_id', $authUser->id)
            ->orWhere('um.receiver_id', $authUser->id)
            ->orderBy('messages.created_at', 'DESC')
            ->get();
    }

    /**
     * index all facilitators that the user is enrolled in their courses for user
     * @return Collection
     */
    public static function indexFacilitatorsChatForUser() : Collection
    {
        $user = Auth::user();
        $enrolled_courses = $user->subscribedCourses()->pluck('course_id');
        $enrolled_courses_facilitators = Course::whereIn('id', $enrolled_courses)->pluck('facilitator_id');
        return User::whereIn('id', $enrolled_courses_facilitators)->with('media')->get();
    }

    /**
     * index all users enrolled in facilitator's courses for facilitator
     * @return Collection
     */
    public static function indexUsersForFacilitatorChat() : Collection
    {
        $user = Auth::user();
        $facilitated_courses = Course::where('facilitator_id', $user->id)->pluck('id');
        $subscribed_users = Course::whereIn('id', $facilitated_courses)->with('subscribers')->get()->pluck('subscribers')->flatten()->pluck('id');
        return User::whereIn('id', $subscribed_users)->with('media')->get();
    }

    /**
     * get unread messages count
     * @return int
     */
    public static function getUnreadMessagesCount() : int
    {
        $authUser = Auth::user();
        return DB::table('messages')
            ->join('user_messages as um', 'messages.id', '=', 'um.message_id')
            ->where('um.receiver_id', $authUser->id)
            ->where('um.is_read', false)
            ->count();
    }

    public static function markAsRead($senderId): void
    {
        $authUser = Auth::user();
        $unreadMessages = DB::table('user_messages')
            ->where('sender_id', $senderId)
            ->where('receiver_id', $authUser->id)
            ->where('is_read', false)
            ->exists();
        if (!$unreadMessages) {
            return;
        }
        DB::table('user_messages')
            ->where('sender_id', $senderId)
            ->where('receiver_id', $authUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

}
