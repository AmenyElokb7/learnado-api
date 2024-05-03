<?php
namespace App\Repositories\Message;

use App\Mail\SupportMessageMail;
use App\Models\SupportMessage;
use App\Events\MessageSent;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class MessageRepository
{
public final function saveMessage($userId, $data) : void
{
    $message= SupportMessage::create([
        'user_id' => $userId,
        'message' => $data['message'],
        'subject' => $data['subject']
    ]);
    event(new MessageSent($message));
    $user= User::find($userId);
    Mail::to($user)->send(new SupportMessageMail($user, $message));
}
}
