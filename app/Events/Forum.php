<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Forum implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user, $picture ,$message, $time;
    private $id;
    /**
     * Create a new event instance.
     */
    public function __construct($message, $user, $picture, $time,$id)
    {
        $this->message = $message;
        $this->user = $user;
        $this->picture = $picture;
        $this->time = $time;
        $this->id = $id;
    }



    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('forum.'. $this->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'forum';
    }
}
