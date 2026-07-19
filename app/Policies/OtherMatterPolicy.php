<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OtherMatter;
use Illuminate\Auth\Access\HandlesAuthorization;

class OtherMatterPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_other::matter');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OtherMatter $otherMatter): bool
    {
        return $user->can('view_other::matter');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_other::matter');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OtherMatter $otherMatter): bool
    {
        return $user->can('update_other::matter');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OtherMatter $otherMatter): bool
    {
        return $user->can('delete_other::matter');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_other::matter');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, OtherMatter $otherMatter): bool
    {
        return $user->can('force_delete_other::matter');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_other::matter');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, OtherMatter $otherMatter): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, OtherMatter $otherMatter): bool
    {
        return $user->can('replicate_other::matter');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_other::matter');
    }
}
