<?php

declare(strict_types=1);

namespace App\Policies\Website;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Website\Security;
use Illuminate\Auth\Access\HandlesAuthorization;

class SecurityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Security');
    }

    public function view(AuthUser $authUser, Security $security): bool
    {
        return $authUser->can('View:Security');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Security');
    }

    public function update(AuthUser $authUser, Security $security): bool
    {
        return $authUser->can('Update:Security');
    }

    public function delete(AuthUser $authUser, Security $security): bool
    {
        return $authUser->can('Delete:Security');
    }

    public function restore(AuthUser $authUser, Security $security): bool
    {
        return $authUser->can('Restore:Security');
    }

    public function forceDelete(AuthUser $authUser, Security $security): bool
    {
        return $authUser->can('ForceDelete:Security');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Security');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Security');
    }

    public function replicate(AuthUser $authUser, Security $security): bool
    {
        return $authUser->can('Replicate:Security');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Security');
    }

}