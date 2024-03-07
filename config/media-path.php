<?php

return [

    'disk' => 'public',
    'App\Models\User' => [
        'path' => 'profile_pictures',

    ],
    'App\Models\Admin' => [
        'path' => 'profile_pictures',

    ],
    'App\Models\Course' => [
        'path' => 'media_courses',

    ],
    'App\Models\Step' => [
        'path' => 'media_steps',

    ],


];
