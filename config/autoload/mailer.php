<?php
return [
    'transport' => [
        'host' => env('MAILER_HOST'),
        'username' => env('MAILER_USERNAME'),
        'password' => env('MAILER_PASSWORD'),
        'port' => env('MAILER_PORT'),
        'encryption' => env('MAILER_ENCRYPTION'),
    ],
    'messageConfig' => [
        'charset' => env('MAILER_CHARSET'),
        'from' => [env('MAILER_FROMADDR') => env('MAILER_FROMNAME')]
    ],
];