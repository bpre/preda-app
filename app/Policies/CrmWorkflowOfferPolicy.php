<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CrmWorkflowOffer;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrmWorkflowOfferPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_crm_workflow_offer');
    }

    public function view(AuthUser $authUser, CrmWorkflowOffer $crmWorkflowOffer): bool
    {
        return $authUser->can('view_crm_workflow_offer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_crm_workflow_offer');
    }

    public function update(AuthUser $authUser, CrmWorkflowOffer $crmWorkflowOffer): bool
    {
        return $authUser->can('update_crm_workflow_offer');
    }

    public function delete(AuthUser $authUser, CrmWorkflowOffer $crmWorkflowOffer): bool
    {
        return $authUser->can('delete_crm_workflow_offer');
    }

    public function restore(AuthUser $authUser, CrmWorkflowOffer $crmWorkflowOffer): bool
    {
        return $authUser->can('restore_crm_workflow_offer');
    }

    public function forceDelete(AuthUser $authUser, CrmWorkflowOffer $crmWorkflowOffer): bool
    {
        return $authUser->can('force_delete_crm_workflow_offer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_crm_workflow_offer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_crm_workflow_offer');
    }

    public function replicate(AuthUser $authUser, CrmWorkflowOffer $crmWorkflowOffer): bool
    {
        return $authUser->can('replicate_crm_workflow_offer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_crm_workflow_offer');
    }

}