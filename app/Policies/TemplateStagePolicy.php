<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TemplateStage;
use Illuminate\Auth\Access\HandlesAuthorization;

class TemplateStagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_template::stage');
    }

    public function view(AuthUser $authUser, TemplateStage $templateStage): bool
    {
        return $authUser->can('view_template::stage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_template::stage');
    }

    public function update(AuthUser $authUser, TemplateStage $templateStage): bool
    {
        return $authUser->can('update_template::stage');
    }

    public function delete(AuthUser $authUser, TemplateStage $templateStage): bool
    {
        return $authUser->can('delete_template::stage');
    }

    public function restore(AuthUser $authUser, TemplateStage $templateStage): bool
    {
        return $authUser->can('restore_template::stage');
    }

    public function forceDelete(AuthUser $authUser, TemplateStage $templateStage): bool
    {
        return $authUser->can('force_delete_template::stage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_template::stage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_template::stage');
    }

    public function replicate(AuthUser $authUser, TemplateStage $templateStage): bool
    {
        return $authUser->can('replicate_template::stage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_template::stage');
    }

}