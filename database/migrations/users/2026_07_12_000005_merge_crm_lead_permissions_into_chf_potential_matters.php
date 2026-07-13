<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissionMap = [
        'view_any_lead' => 'view_any_c::h::f::potential::matter',
        'view_lead' => 'view_c::h::f::potential::matter',
        'create_lead' => 'create_c::h::f::potential::matter',
        'update_lead' => 'update_c::h::f::potential::matter',
        'delete_lead' => 'delete_c::h::f::potential::matter',
        'delete_any_lead' => 'delete_any_c::h::f::potential::matter',
        'force_delete_lead' => 'force_delete_c::h::f::potential::matter',
        'force_delete_any_lead' => 'force_delete_any_c::h::f::potential::matter',
        'restore_lead' => 'restore_c::h::f::potential::matter',
        'restore_any_lead' => 'restore_any_c::h::f::potential::matter',
        'replicate_lead' => 'replicate_c::h::f::potential::matter',
        'reorder_lead' => 'reorder_c::h::f::potential::matter',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        foreach ($this->permissionMap as $oldName => $newName) {
            DB::table('permissions')
                ->where('name', $oldName)
                ->orderBy('id')
                ->get()
                ->each(function (object $oldPermission) use ($newName): void {
                    $newPermissionId = $this->ensurePermission($newName, $oldPermission->guard_name);

                    $this->copyAssignments('role_has_permissions', (int) $oldPermission->id, $newPermissionId);
                    $this->copyAssignments('model_has_permissions', (int) $oldPermission->id, $newPermissionId);
                });
        }

        $oldPermissionIds = DB::table('permissions')
            ->whereIn('name', array_keys($this->permissionMap))
            ->pluck('id');

        if ($oldPermissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')
                ->whereIn('permission_id', $oldPermissionIds)
                ->delete();

            DB::table('model_has_permissions')
                ->whereIn('permission_id', $oldPermissionIds)
                ->delete();

            DB::table('permissions')
                ->whereIn('id', $oldPermissionIds)
                ->delete();
        }

        if (class_exists(PermissionRegistrar::class)) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        // Intentionally irreversible: CRM leads were merged into one Szanse resource.
    }

    private function ensurePermission(string $name, string $guardName): int
    {
        $permission = DB::table('permissions')
            ->where('name', $name)
            ->where('guard_name', $guardName)
            ->first();

        if ($permission) {
            return (int) $permission->id;
        }

        return (int) DB::table('permissions')->insertGetId([
            'name' => $name,
            'guard_name' => $guardName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function copyAssignments(string $table, int $oldPermissionId, int $newPermissionId): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)
            ->where('permission_id', $oldPermissionId)
            ->orderBy('permission_id')
            ->get()
            ->each(function (object $assignment) use ($table, $newPermissionId): void {
                $data = (array) $assignment;
                $data['permission_id'] = $newPermissionId;

                DB::table($table)->insertOrIgnore($data);
            });
    }
};
