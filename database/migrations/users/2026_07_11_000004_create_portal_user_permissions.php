<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $rolesTable = config('permission.table_names.roles', 'roles');
        $roleHasPermissionsTable = config('permission.table_names.role_has_permissions', 'role_has_permissions');

        if (! Schema::hasTable($permissionsTable) || ! Schema::hasTable($rolesTable) || ! Schema::hasTable($roleHasPermissionsTable)) {
            return;
        }

        $now = now();
        $permissionNames = [
            'view_any_portal::user',
            'view_portal::user',
            'create_portal::user',
            'update_portal::user',
        ];

        DB::table($permissionsTable)->insertOrIgnore(
            array_map(
                fn (string $permissionName): array => [
                    'name' => $permissionName,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                $permissionNames,
            ),
        );

        $superAdminRoleId = DB::table($rolesTable)
            ->where('name', config('filament-shield.super_admin.name', 'super_admin'))
            ->where('guard_name', 'web')
            ->value('id');

        if (! $superAdminRoleId) {
            return;
        }

        $permissionIds = DB::table($permissionsTable)
            ->whereIn('name', $permissionNames)
            ->where('guard_name', 'web')
            ->pluck('id')
            ->all();

        DB::table($roleHasPermissionsTable)->insertOrIgnore(
            array_map(
                fn (int $permissionId): array => [
                    'permission_id' => $permissionId,
                    'role_id' => $superAdminRoleId,
                ],
                $permissionIds,
            ),
        );
    }

    public function down(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $roleHasPermissionsTable = config('permission.table_names.role_has_permissions', 'role_has_permissions');

        if (! Schema::hasTable($permissionsTable) || ! Schema::hasTable($roleHasPermissionsTable)) {
            return;
        }

        $permissionIds = DB::table($permissionsTable)
            ->whereIn('name', [
                'view_any_portal::user',
                'view_portal::user',
                'create_portal::user',
                'update_portal::user',
            ])
            ->where('guard_name', 'web')
            ->pluck('id')
            ->all();

        DB::table($roleHasPermissionsTable)
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        DB::table($permissionsTable)
            ->whereIn('id', $permissionIds)
            ->delete();
    }
};
