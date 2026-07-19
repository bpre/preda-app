<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CrmMailTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrmMailTemplatePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_crm_mail_template');
    }

    public function view(AuthUser $authUser, CrmMailTemplate $crmMailTemplate): bool
    {
        return $authUser->can('view_crm_mail_template');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_crm_mail_template');
    }

    public function update(AuthUser $authUser, CrmMailTemplate $crmMailTemplate): bool
    {
        return $authUser->can('update_crm_mail_template');
    }

    public function delete(AuthUser $authUser, CrmMailTemplate $crmMailTemplate): bool
    {
        return $authUser->can('delete_crm_mail_template');
    }

    public function restore(AuthUser $authUser, CrmMailTemplate $crmMailTemplate): bool
    {
        return $authUser->can('restore_crm_mail_template');
    }

    public function forceDelete(AuthUser $authUser, CrmMailTemplate $crmMailTemplate): bool
    {
        return $authUser->can('force_delete_crm_mail_template');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_crm_mail_template');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_crm_mail_template');
    }

    public function replicate(AuthUser $authUser, CrmMailTemplate $crmMailTemplate): bool
    {
        return $authUser->can('replicate_crm_mail_template');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_crm_mail_template');
    }

}