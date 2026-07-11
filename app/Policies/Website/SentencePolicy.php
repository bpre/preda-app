<?php

declare(strict_types=1);

namespace App\Policies\Website;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Website\Sentence;
use Illuminate\Auth\Access\HandlesAuthorization;

class SentencePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Sentence');
    }

    public function view(AuthUser $authUser, Sentence $sentence): bool
    {
        return $authUser->can('View:Sentence');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Sentence');
    }

    public function update(AuthUser $authUser, Sentence $sentence): bool
    {
        return $authUser->can('Update:Sentence');
    }

    public function delete(AuthUser $authUser, Sentence $sentence): bool
    {
        return $authUser->can('Delete:Sentence');
    }

    public function restore(AuthUser $authUser, Sentence $sentence): bool
    {
        return $authUser->can('Restore:Sentence');
    }

    public function forceDelete(AuthUser $authUser, Sentence $sentence): bool
    {
        return $authUser->can('ForceDelete:Sentence');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Sentence');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Sentence');
    }

    public function replicate(AuthUser $authUser, Sentence $sentence): bool
    {
        return $authUser->can('Replicate:Sentence');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Sentence');
    }

}