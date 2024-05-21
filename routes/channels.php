<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('forum.{id}', function ($user, $id) {
    return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('forum.{learningPathId}', function ($user, $learningPathId) {
    return ['id' => $user->id, 'name' => $user->name];
});
Broadcast::channel('private.message', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});


