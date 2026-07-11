<?php

return [
    'scheduler' => [
        'mode' => env('LETTER_NOTIFICATION_SCHEDULE_MODE', 'daily_at_time'),
        'send_time' => env('LETTER_NOTIFICATION_SEND_TIME', '18:00'),
    ],
];
