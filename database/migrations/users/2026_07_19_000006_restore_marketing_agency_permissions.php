<?php

use App\Services\Crm\LeadStatsService;
use App\Support\Crm\MarketingAgencyAccess;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            MarketingAgencyAccess::VIEW_LEAD_STATS_PERMISSION,
            MarketingAgencyAccess::VIEW_MARKETING_LEADS_PERMISSION,
            LeadStatsService::EXPORT_PERMISSION,
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $role = Role::query()
            ->where('name', MarketingAgencyAccess::ROLE)
            ->where('guard_name', 'web')
            ->first();

        $role?->givePermissionTo($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
