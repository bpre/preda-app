<?php

declare(strict_types=1);

namespace App\Policies\Website;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Website\PageSnapshot;
use Illuminate\Auth\Access\HandlesAuthorization;

class PageSnapshotPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PageSnapshot');
    }

    public function view(AuthUser $authUser, PageSnapshot $pageSnapshot): bool
    {
        return $authUser->can('View:PageSnapshot');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PageSnapshot');
    }

    public function update(AuthUser $authUser, PageSnapshot $pageSnapshot): bool
    {
        return $authUser->can('Update:PageSnapshot');
    }

    public function delete(AuthUser $authUser, PageSnapshot $pageSnapshot): bool
    {
        return $authUser->can('Delete:PageSnapshot');
    }

    public function restore(AuthUser $authUser, PageSnapshot $pageSnapshot): bool
    {
        return $authUser->can('Restore:PageSnapshot');
    }

    public function forceDelete(AuthUser $authUser, PageSnapshot $pageSnapshot): bool
    {
        return $authUser->can('ForceDelete:PageSnapshot');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PageSnapshot');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PageSnapshot');
    }

    public function replicate(AuthUser $authUser, PageSnapshot $pageSnapshot): bool
    {
        return $authUser->can('Replicate:PageSnapshot');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PageSnapshot');
    }

}