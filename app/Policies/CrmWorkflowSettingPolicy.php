<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CrmWorkflowSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrmWorkflowSettingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_crm_workflow_setting');
    }

    public function view(AuthUser $authUser, CrmWorkflowSetting $crmWorkflowSetting): bool
    {
        return $authUser->can('view_crm_workflow_setting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_crm_workflow_setting');
    }

    public function update(AuthUser $authUser, CrmWorkflowSetting $crmWorkflowSetting): bool
    {
        return $authUser->can('update_crm_workflow_setting');
    }

    public function delete(AuthUser $authUser, CrmWorkflowSetting $crmWorkflowSetting): bool
    {
        return $authUser->can('delete_crm_workflow_setting');
    }

    public function restore(AuthUser $authUser, CrmWorkflowSetting $crmWorkflowSetting): bool
    {
        return $authUser->can('restore_crm_workflow_setting');
    }

    public function forceDelete(AuthUser $authUser, CrmWorkflowSetting $crmWorkflowSetting): bool
    {
        return $authUser->can('force_delete_crm_workflow_setting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_crm_workflow_setting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_crm_workflow_setting');
    }

    public function replicate(AuthUser $authUser, CrmWorkflowSetting $crmWorkflowSetting): bool
    {
        return $authUser->can('replicate_crm_workflow_setting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_crm_workflow_setting');
    }

}