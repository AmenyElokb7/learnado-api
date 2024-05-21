<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $dateFormat = 'U';

    protected $fillable = ['message', 'created_at', 'updated_at'];


    public function senders() {
        return $this->belongsToMany(User::class, 'user_messages', 'message_id', 'sender_id');
    }

    public function receivers() {
        return $this->belongsToMany(User::class, 'user_messages', 'message_id', 'receiver_id');
    }

}
