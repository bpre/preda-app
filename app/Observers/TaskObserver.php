<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\Notification;

class TaskObserver
{
    public function creating(Task $task): void
    {
        $task->created_by = auth()->user()->id;
        $task->assigned_to = $task->is_private ? auth()->user()->id : $task->assigned_to;
    }

    public function deleting(Task $task): void
    {
        $task->comments()->each(fn ($comment) => $comment->delete());
        Notification::where('data->task', $task->id)->delete();
    }

}
