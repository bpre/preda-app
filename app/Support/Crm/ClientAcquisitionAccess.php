<?php

namespace App\Support\Crm;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class ClientAcquisitionAccess
{
    public const ROLE = 'partner';

    public static function canUse(?Authenticatable $user = null): bool
    {
        $user ??= auth()->user();

        return $user instanceof User
            && $user->is_active
            && $user->is_employee
            && $user->hasRole(self::ROLE);
    }
}
