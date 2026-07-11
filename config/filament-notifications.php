<?php

use App\Models\User;
use App\Notifications\TaskDone;
use App\Notifications\TaskCreated;
use App\Notifications\TaskReopened;
use App\Notifications\TaskCommentAdded;
use App\Filament\Resources\NotificationResource;
use RalphJSmit\Filament\Notifications\Models\DatabaseNotification;
use RalphJSmit\Filament\Notifications\Filament\Pages\Notifications;
// use RalphJSmit\Filament\Notifications\Filament\Resources\NotificationResource;

return [
    'notifications' => [
        // Add the notification classes that your users are allowed to send.
        // \App\Notifications\TestNotification::class,
        TaskCreated::class,
        TaskReopened::class,
        TaskDone::class,
        TaskCommentAdded::class
    ],

    'notifiables' => [
        'classes' => [
            // The models that can receive notifications.
            User::class,
        ],

        'title-attributes' => [
            // A display-friendly attribute that should be used in the NotificationResource to display each record.
            User::class => 'name',
        ],
    ],

    'register' => [
        'models' => [
            'database-notification' => DatabaseNotification::class,
        ],
        'resources' => [
            'notifications' => NotificationResource::class,
        ],
        'pages' => [
            // Notifications::class,
        ],
    ],
];
