<?php

use App\Services\UserImpersonationService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_impersonation_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('impersonator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('impersonated_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->text('start_url')->nullable();
            $table->text('stop_url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        $this->createPermission();
    }

    public function down(): void
    {
        Schema::dropIfExists('user_impersonation_logs');

        $this->deletePermission();
    }

    private function createPermission(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $rolesTable = config('permission.table_names.roles', 'roles');
        $roleHasPermissionsTable = config('permission.table_names.role_has_permissions', 'role_has_permissions');

        if (! Schema::hasTable($permissionsTable) || ! Schema::hasTable($rolesTable) || ! Schema::hasTable($roleHasPermissionsTable)) {
            return;
        }

        $now = now();

        DB::table($permissionsTable)->insertOrIgnore([
            'name' => UserImpersonationService::PERMISSION,
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $superAdminRoleId = DB::table($rolesTable)
            ->where('name', config('filament-shield.super_admin.name', 'super_admin'))
            ->where('guard_name', 'web')
            ->value('id');

        if (! $superAdminRoleId) {
            return;
        }

        $permissionId = DB::table($permissionsTable)
            ->where('name', UserImpersonationService::PERMISSION)
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId) {
            return;
        }

        DB::table($roleHasPermissionsTable)->insertOrIgnore([
            'permission_id' => $permissionId,
            'role_id' => $superAdminRoleId,
        ]);

        $this->forgetCachedPermissions();
    }

    private function deletePermission(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $roleHasPermissionsTable = config('permission.table_names.role_has_permissions', 'role_has_permissions');

        if (! Schema::hasTable($permissionsTable) || ! Schema::hasTable($roleHasPermissionsTable)) {
            return;
        }

        $permissionId = DB::table($permissionsTable)
            ->where('name', UserImpersonationService::PERMISSION)
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId) {
            return;
        }

        DB::table($roleHasPermissionsTable)
            ->where('permission_id', $permissionId)
            ->delete();

        DB::table($permissionsTable)
            ->where('id', $permissionId)
            ->delete();

        $this->forgetCachedPermissions();
    }

    private function forgetCachedPermissions(): void
    {
        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
};
