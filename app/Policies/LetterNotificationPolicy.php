<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LetterNotification;
use Illuminate\Auth\Access\HandlesAuthorization;

class LetterNotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_letter::notification');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LetterNotification $letterNotification): bool
    {
        return $user->can('view_letter::notification');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_letter::notification');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LetterNotification $letterNotification): bool
    {
        return $user->can('update_letter::notification');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LetterNotification $letterNotification): bool
    {
        return $user->can('delete_letter::notification');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_letter::notification');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, LetterNotification $letterNotification): bool
    {
        return $user->can('force_delete_letter::notification');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_letter::notification');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, LetterNotification $letterNotification): bool
    {
        return $user->can('restore_letter::notification');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_letter::notification');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, LetterNotification $letterNotification): bool
    {
        return $user->can('replicate_letter::notification');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_letter::notification');
    }
}
