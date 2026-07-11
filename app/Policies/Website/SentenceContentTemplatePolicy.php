<?php

declare(strict_types=1);

namespace App\Policies\Website;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Website\SentenceContentTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class SentenceContentTemplatePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SentenceContentTemplate');
    }

    public function view(AuthUser $authUser, SentenceContentTemplate $sentenceContentTemplate): bool
    {
        return $authUser->can('View:SentenceContentTemplate');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SentenceContentTemplate');
    }

    public function update(AuthUser $authUser, SentenceContentTemplate $sentenceContentTemplate): bool
    {
        return $authUser->can('Update:SentenceContentTemplate');
    }

    public function delete(AuthUser $authUser, SentenceContentTemplate $sentenceContentTemplate): bool
    {
        return $authUser->can('Delete:SentenceContentTemplate');
    }

    public function restore(AuthUser $authUser, SentenceContentTemplate $sentenceContentTemplate): bool
    {
        return $authUser->can('Restore:SentenceContentTemplate');
    }

    public function forceDelete(AuthUser $authUser, SentenceContentTemplate $sentenceContentTemplate): bool
    {
        return $authUser->can('ForceDelete:SentenceContentTemplate');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SentenceContentTemplate');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SentenceContentTemplate');
    }

    public function replicate(AuthUser $authUser, SentenceContentTemplate $sentenceContentTemplate): bool
    {
        return $authUser->can('Replicate:SentenceContentTemplate');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SentenceContentTemplate');
    }

}