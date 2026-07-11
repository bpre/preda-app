<?php

namespace App\Policies;

use App\Models\PortalUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PortalUserPolicy
{
    use HandlesAuthorization;

    public function before(User $user): ?bool
    {
        if ($user->hasRole(config('filament-shield.super_admin.name', 'super_admin'))) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_portal::user');
    }

    public function view(User $user, PortalUser $portalUser): bool
    {
        return $user->can('view_portal::user');
    }

    public function create(User $user): bool
    {
        return $user->can('create_portal::user');
    }

    public function update(User $user, PortalUser $portalUser): bool
    {
        return $user->can('update_portal::user');
    }

    public function delete(User $user, PortalUser $portalUser): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, PortalUser $portalUser): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, PortalUser $portalUser): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function replicate(User $user, PortalUser $portalUser): bool
    {
        return false;
    }

    public function reorder(User $user): bool
    {
        return false;
    }
}
