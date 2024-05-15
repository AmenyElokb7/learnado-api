<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('forum.{courseId}', function ($user, $courseId) {
    \Illuminate\Support\Facades\Log::info("Authorizing user for forum.course channel", ['user' => $user, 'courseId' => $courseId]);
    return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('forum.{learningPathId}', function ($user, $learningPathId) {
    \Illuminate\Support\Facades\Log::info("Authorizing user for forum.learningPath channel", ['user' => $user, 'learningPathId' => $learningPathId]);
    return ['id' => $user->id, 'name' => $user->name];
});


