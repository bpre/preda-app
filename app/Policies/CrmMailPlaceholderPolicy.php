<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CrmMailPlaceholder;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrmMailPlaceholderPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_crm_mail_placeholder');
    }

    public function view(AuthUser $authUser, CrmMailPlaceholder $crmMailPlaceholder): bool
    {
        return $authUser->can('view_crm_mail_placeholder');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_crm_mail_placeholder');
    }

    public function update(AuthUser $authUser, CrmMailPlaceholder $crmMailPlaceholder): bool
    {
        return $authUser->can('update_crm_mail_placeholder');
    }

    public function delete(AuthUser $authUser, CrmMailPlaceholder $crmMailPlaceholder): bool
    {
        return $authUser->can('delete_crm_mail_placeholder');
    }

    public function restore(AuthUser $authUser, CrmMailPlaceholder $crmMailPlaceholder): bool
    {
        return $authUser->can('restore_crm_mail_placeholder');
    }

    public function forceDelete(AuthUser $authUser, CrmMailPlaceholder $crmMailPlaceholder): bool
    {
        return $authUser->can('force_delete_crm_mail_placeholder');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_crm_mail_placeholder');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_crm_mail_placeholder');
    }

    public function replicate(AuthUser $authUser, CrmMailPlaceholder $crmMailPlaceholder): bool
    {
        return $authUser->can('replicate_crm_mail_placeholder');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_crm_mail_placeholder');
    }

}