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
    'App\Models\LearningPath' => [
        'path' => 'media_learning_paths',
    ],
    'App\Models\Category' => [
        'path' => 'media_categories',
    ],
];
