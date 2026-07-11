<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use RalphJSmit\Filament\Notifications\FilamentNotification;
use RalphJSmit\Filament\Notifications\Contracts\AsFilamentNotification;
use RalphJSmit\Filament\Notifications\Concerns\StoresNotificationInDatabase;

class TaskCommentAdded extends Notification implements AsFilamentNotification
{
    use Queueable;
    use StoresNotificationInDatabase;

    public function __construct(
        public Task $task,
        public string $comment,
    ) {}

    public static function toFilamentNotification(): FilamentNotification
    {
        return FilamentNotification::make()
            ->message(fn (self $notification) => "Nowy komentarz do zadania")
            ->description(fn (self $notification) =>
            ($notification->task->matter ? "Sprawa: ".e($notification->task->matter?->label)."<br>" : "")."
            Zadanie: ".e($notification->task->label)."<br>
            Komentarz: ".e($notification->comment)."<br>
            Dodany przez: ".e($notification->task->task_creator->name)."
            "

            , 'above');
    }

    public function via($notifiable): array
    {
        return ['database'];
    }
}
