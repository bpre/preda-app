<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CHFPotentialMatter;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CHFPotentialMatterPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(User $authUser): bool
    {
        return $authUser->can('view_any_c::h::f::potential::matter');
    }

    public function view(User $authUser, CHFPotentialMatter $cHFPotentialMatter): bool
    {
        return $authUser->can('view_c::h::f::potential::matter');
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('create_c::h::f::potential::matter');
    }

    public function update(User $authUser, CHFPotentialMatter $cHFPotentialMatter): bool
    {
        return $authUser->can('update_c::h::f::potential::matter');
    }

    public function delete(User $authUser, CHFPotentialMatter $cHFPotentialMatter): bool
    {
        return $authUser->can('delete_c::h::f::potential::matter');
    }

    public function restore(User $authUser, CHFPotentialMatter $cHFPotentialMatter): bool
    {
        return $authUser->isAdmin();
    }

    public function forceDelete(User $authUser, CHFPotentialMatter $cHFPotentialMatter): bool
    {
        return $authUser->can('force_delete_c::h::f::potential::matter');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->can('force_delete_any_c::h::f::potential::matter');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->isAdmin();
    }

    public function replicate(User $authUser, CHFPotentialMatter $cHFPotentialMatter): bool
    {
        return $authUser->can('replicate_c::h::f::potential::matter');
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->can('reorder_c::h::f::potential::matter');
    }

}
