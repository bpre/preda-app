<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CrmWorkflowRule;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrmWorkflowRulePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_crm_workflow_rule');
    }

    public function view(AuthUser $authUser, CrmWorkflowRule $crmWorkflowRule): bool
    {
        return $authUser->can('view_crm_workflow_rule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_crm_workflow_rule');
    }

    public function update(AuthUser $authUser, CrmWorkflowRule $crmWorkflowRule): bool
    {
        return $authUser->can('update_crm_workflow_rule');
    }

    public function delete(AuthUser $authUser, CrmWorkflowRule $crmWorkflowRule): bool
    {
        return $authUser->can('delete_crm_workflow_rule');
    }

    public function restore(AuthUser $authUser, CrmWorkflowRule $crmWorkflowRule): bool
    {
        return $authUser->can('restore_crm_workflow_rule');
    }

    public function forceDelete(AuthUser $authUser, CrmWorkflowRule $crmWorkflowRule): bool
    {
        return $authUser->can('force_delete_crm_workflow_rule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_crm_workflow_rule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_crm_workflow_rule');
    }

    public function replicate(AuthUser $authUser, CrmWorkflowRule $crmWorkflowRule): bool
    {
        return $authUser->can('replicate_crm_workflow_rule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_crm_workflow_rule');
    }

}