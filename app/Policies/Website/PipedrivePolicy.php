<?php

declare(strict_types=1);

namespace App\Policies\Website;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Website\Pipedrive;
use Illuminate\Auth\Access\HandlesAuthorization;

class PipedrivePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Pipedrive');
    }

    public function view(AuthUser $authUser, Pipedrive $pipedrive): bool
    {
        return $authUser->can('View:Pipedrive');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Pipedrive');
    }

    public function update(AuthUser $authUser, Pipedrive $pipedrive): bool
    {
        return $authUser->can('Update:Pipedrive');
    }

    public function delete(AuthUser $authUser, Pipedrive $pipedrive): bool
    {
        return $authUser->can('Delete:Pipedrive');
    }

    public function restore(AuthUser $authUser, Pipedrive $pipedrive): bool
    {
        return $authUser->can('Restore:Pipedrive');
    }

    public function forceDelete(AuthUser $authUser, Pipedrive $pipedrive): bool
    {
        return $authUser->can('ForceDelete:Pipedrive');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Pipedrive');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Pipedrive');
    }

    public function replicate(AuthUser $authUser, Pipedrive $pipedrive): bool
    {
        return $authUser->can('Replicate:Pipedrive');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Pipedrive');
    }

}