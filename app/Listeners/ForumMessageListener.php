<?php

namespace App\Listeners;

use App\Repositories\Message\MessageRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ForumMessageListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {

        Log::info('Handling ForumMessageListener', [
            'message' => $event->message,
            'learningPathId' => $event->learningPathId,
            'courseId' => $event->courseId
        ]);

    }
}
