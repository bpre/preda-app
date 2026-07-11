<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $modelHasPermissionsTable = config('permission.table_names.model_has_permissions', 'model_has_permissions');
        $modelMorphKey = config('permission.column_names.model_morph_key', 'model_id');

        if (! Schema::hasTable($permissionsTable) || ! Schema::hasTable($modelHasPermissionsTable)) {
            return;
        }

        $now = now();
        $permissionNames = [
            'access_kancelaria_panel',
            'access_crm_panel',
            'access_cms_panel',
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

        $permissionIds = DB::table($permissionsTable)
            ->whereIn('name', $permissionNames)
            ->where('guard_name', 'web')
            ->pluck('id')
            ->all();

        $employeeIds = DB::table('users')
            ->where('is_active', true)
            ->where('is_employee', true)
            ->pluck('id')
            ->all();

        $rows = [];

        foreach ($employeeIds as $employeeId) {
            foreach ($permissionIds as $permissionId) {
                $rows[] = [
                    'permission_id' => $permissionId,
                    'model_type' => User::class,
                    $modelMorphKey => $employeeId,
                ];
            }
        }

        if ($rows !== []) {
            DB::table($modelHasPermissionsTable)->insertOrIgnore($rows);
        }
    }

    public function down(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $modelHasPermissionsTable = config('permission.table_names.model_has_permissions', 'model_has_permissions');

        if (! Schema::hasTable($permissionsTable) || ! Schema::hasTable($modelHasPermissionsTable)) {
            return;
        }

        $permissionIds = DB::table($permissionsTable)
            ->whereIn('name', [
                'access_kancelaria_panel',
                'access_crm_panel',
                'access_cms_panel',
            ])
            ->where('guard_name', 'web')
            ->pluck('id')
            ->all();

        DB::table($modelHasPermissionsTable)
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        DB::table($permissionsTable)
            ->whereIn('id', $permissionIds)
            ->delete();
    }
};
