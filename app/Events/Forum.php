<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Forum implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $learningPathId, $courseId ,$message;
    /**
     * Create a new event instance.
     */
    public function __construct($message, $learningPathId, $courseId)
    {
        Log::info("Dispatching Forum event", [
            'message' => $message,
            'learningPathId' => $learningPathId,
            'courseId' => $courseId
        ]);
        $this->message = $message;
        $this->learningPathId = $learningPathId;
        $this->courseId = $courseId;
    }

    public function broadcastOn()
    {
        if ($this->courseId) {
            return new PresenceChannel('forum.' . $this->courseId);
        }
        return new PresenceChannel('forum.' . $this->learningPathId);
    }



}
