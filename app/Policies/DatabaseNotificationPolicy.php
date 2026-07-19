<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use RalphJSmit\Filament\Notifications\Models\DatabaseNotification;
use Illuminate\Auth\Access\HandlesAuthorization;

class DatabaseNotificationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_notification');
    }

    public function view(AuthUser $authUser, DatabaseNotification $databaseNotification): bool
    {
        return $authUser->can('view_notification');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_notification');
    }

    public function update(AuthUser $authUser, DatabaseNotification $databaseNotification): bool
    {
        return $authUser->can('update_notification');
    }

    public function delete(AuthUser $authUser, DatabaseNotification $databaseNotification): bool
    {
        return $authUser->can('delete_notification');
    }

    public function restore(AuthUser $authUser, DatabaseNotification $databaseNotification): bool
    {
        return $authUser->can('restore_notification');
    }

    public function forceDelete(AuthUser $authUser, DatabaseNotification $databaseNotification): bool
    {
        return $authUser->can('force_delete_notification');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_notification');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_notification');
    }

    public function replicate(AuthUser $authUser, DatabaseNotification $databaseNotification): bool
    {
        return $authUser->can('replicate_notification');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_notification');
    }

}