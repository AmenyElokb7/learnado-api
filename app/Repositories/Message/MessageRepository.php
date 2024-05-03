<?php
namespace App\Repositories\Message;

use App\Helpers\QueryConfig;
use App\Mail\SupportMessageMail;
use App\Models\SupportMessage;
use App\Events\MessageSent;
use App\Models\User;
use App\Traits\PaginationParams;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\LengthRequiredHttpException;

class MessageRepository
{
    use PaginationParams;

    public final function saveMessage($userId, $data): void
    {
        $message = SupportMessage::create([
            'user_id' => $userId,
            'message' => $data['message'],
            'subject' => $data['subject']
        ]);
        event(new MessageSent($message));
        $user = User::find($userId);
        Mail::to($user)->send(new SupportMessageMail($user, $message));
    }

    public static function indexAdminNotifications(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {

        $query = SupportMessage::with('user.media')->newQuery();
        SupportMessage::applyFilters($queryConfig->getFilters(), $query);
        $messages = $query->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())->get();
        $messages->unread = $query->where('is_read', 0)->count();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($messages, $queryConfig);
        }
        return $messages;
    }
}
