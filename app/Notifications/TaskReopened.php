<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\TextInput;
use Illuminate\Notifications\Notification;
use RalphJSmit\Filament\Notifications\FilamentNotification;
use RalphJSmit\Filament\Notifications\Contracts\AsFilamentNotification;
use RalphJSmit\Filament\Notifications\Concerns\StoresNotificationInDatabase;

class TaskReopened extends Notification implements AsFilamentNotification
{
    use Queueable;
    use StoresNotificationInDatabase;

    public function __construct(
        public Task $task,
    ) {}

    public static function toFilamentNotification(): FilamentNotification
    {
        return FilamentNotification::make()
            ->message(fn (self $notification) => "Wznowiono pracę nad zadaniem")
            ->description(fn (self $notification) =>
            ($notification->task->matter ? "Sprawa: {$notification->task->matter?->label}<br>" : "")."
            Zadanie: {$notification->task->label}<br>
            Kto: {$notification->task->assignee->name}
            "

            , 'above');
    }

    public function via($notifiable): array
    {
        return ['database'];
    }
}
