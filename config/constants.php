<?php

return [
    'PAGINATE' => [
        'DEFAULT_PAGE' => 1,
        'DEFAULT_PER_PAGE' => 10,
        'DEFAULT_ORDER_BY' => 'created_at',
        'DEFAULT_DIRECTION' => 'DESC',
        'DEFAULT_PAGINATION' => 1,
    ],
    'MIME_TYPES' => 'jpg,png,jpeg,gif,,svg,PNG,JPG,JPEG,GIF,SVG',
    'MAX_FILE_SIZE' => 5000, // 5MB

    'MIN_PASSWORD_LENGTH' => 8,
    'PASSWORD_REGEX' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
    'MAX_STRING_LENGTH' => 255,
    'MEDIA_MIMES' => 'jpg,jpeg,png,bmp,gif,svg,webp,mp4,mp3',
    'CURRENCY_MIN_VALUE' => 0,

];

