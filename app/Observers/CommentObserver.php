<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\Task;
use App\Notifications\TaskCommentAdded;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        if ($comment->subject_type !== Task::class) {
            return;
        }

        $task = Task::find($comment->subject_id);

        if (! $task) {
            return;
        }

        collect([$task->task_creator, $task->assignee])
            ->filter()
            ->unique('id')
            ->reject(fn ($user) => $user->id === $comment->user_id)
            ->each(fn ($user) => $user->notify(new TaskCommentAdded($task, $comment->comment)));
    }

    /**
     * Handle the Comment "updated" event.
     */
    public function updated($comment): void
    {
        //
    }

    /**
     * Handle the Comment "deleted" event.
     */
    public function deleted($comment): void
    {
        //
    }

    /**
     * Handle the Comment "restored" event.
     */
    public function restored($comment): void
    {
        //
    }

    /**
     * Handle the Comment "force deleted" event.
     */
    public function forceDeleted($comment): void
    {
        //
    }
}
