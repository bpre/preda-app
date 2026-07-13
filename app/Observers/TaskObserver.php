<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\User;
use App\Models\Notification;
use App\Notifications\TaskCreated;

class TaskObserver
{
    public function creating(Task $task): void
    {
        $userId = auth()->id();

        $task->created_by = $userId ?? $task->created_by;
        $task->assigned_to = $task->is_private ? ($userId ?? $task->assigned_to) : $task->assigned_to;
    }

    public function created(Task $task): void
    {
        if (
            blank($task->assigned_to)
            || blank($task->created_by)
            || (string) $task->assigned_to === (string) $task->created_by
        ) {
            return;
        }

        User::query()
            ->find($task->assigned_to)
            ?->notify(new TaskCreated($task));
    }

    public function deleting(Task $task): void
    {
        $task->comments()->each(fn ($comment) => $comment->delete());
        Notification::where('data->task', $task->id)->delete();
    }

}
