<?php

return [
    //otp
    'otp' => [
        'TYPE' => 'numeric',
        'LENGTH' => 6,
        'VALID' => 3,
    ],
    //token
    'days_expire' => 7,
    //image
    'image_max' => 200,
    'image_types' => [
        'png',
        'jpg',
        'jpeg',
    ],
];
