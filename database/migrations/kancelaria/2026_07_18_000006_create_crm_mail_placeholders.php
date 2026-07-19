<?php

use App\Models\CrmMailPlaceholder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const PERMISSIONS = [
        'view_any_crm_mail_placeholder',
        'view_crm_mail_placeholder',
        'update_crm_mail_placeholder',
    ];

    public function up(): void
    {
        $this->createTable();
        $this->ensurePlaceholders();
        $this->createPermissions();
    }

    public function down(): void
    {
        $this->deletePermissions();

        Schema::dropIfExists('crm_mail_placeholders');
    }

    private function createTable(): void
    {
        if (Schema::hasTable('crm_mail_placeholders')) {
            return;
        }

        Schema::create('crm_mail_placeholders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('variants');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort']);
        });
    }

    private function ensurePlaceholders(): void
    {
        $now = now();

        foreach (CrmMailPlaceholder::DEFINITIONS as $key => $definition) {
            $existingId = DB::table('crm_mail_placeholders')
                ->where('key', $key)
                ->value('id');

            DB::table('crm_mail_placeholders')->updateOrInsert(
                ['key' => $key],
                [
                    'id' => $existingId ?: (string) Str::uuid(),
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'variants' => json_encode(CrmMailPlaceholder::defaultVariants($key)),
                    'is_active' => true,
                    'sort' => $definition['sort'],
                    'created_at' => $existingId
                        ? DB::table('crm_mail_placeholders')->where('id', $existingId)->value('created_at')
                        : $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    private function createPermissions(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $rolesTable = config('permission.table_names.roles', 'roles');
        $roleHasPermissionsTable = config('permission.table_names.role_has_permissions', 'role_has_permissions');

        if (! Schema::hasTable($permissionsTable) || ! Schema::hasTable($rolesTable) || ! Schema::hasTable($roleHasPermissionsTable)) {
            return;
        }

        $now = now();

        DB::table($permissionsTable)->insertOrIgnore(array_map(
            fn (string $permissionName): array => [
                'name' => $permissionName,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            self::PERMISSIONS,
        ));

        $superAdminRoleId = DB::table($rolesTable)
            ->where('name', config('filament-shield.super_admin.name', 'super_admin'))
            ->where('guard_name', 'web')
            ->value('id');

        if (! $superAdminRoleId) {
            return;
        }

        $permissionIds = DB::table($permissionsTable)
            ->whereIn('name', self::PERMISSIONS)
            ->where('guard_name', 'web')
            ->pluck('id')
            ->all();

        DB::table($roleHasPermissionsTable)->insertOrIgnore(array_map(
            fn (int $permissionId): array => [
                'permission_id' => $permissionId,
                'role_id' => $superAdminRoleId,
            ],
            $permissionIds,
        ));

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    private function deletePermissions(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $roleHasPermissionsTable = config('permission.table_names.role_has_permissions', 'role_has_permissions');

        if (! Schema::hasTable($permissionsTable) || ! Schema::hasTable($roleHasPermissionsTable)) {
            return;
        }

        $permissionIds = DB::table($permissionsTable)
            ->whereIn('name', self::PERMISSIONS)
            ->where('guard_name', 'web')
            ->pluck('id')
            ->all();

        DB::table($roleHasPermissionsTable)
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        DB::table($permissionsTable)
            ->whereIn('id', $permissionIds)
            ->delete();

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
};
