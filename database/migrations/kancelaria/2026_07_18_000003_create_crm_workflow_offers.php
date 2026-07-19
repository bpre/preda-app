<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const PERMISSIONS = [
        'view_any_crm_workflow_offer',
        'view_crm_workflow_offer',
        'create_crm_workflow_offer',
        'update_crm_workflow_offer',
        'delete_crm_workflow_offer',
        'delete_any_crm_workflow_offer',
    ];

    public function up(): void
    {
        $this->createOffersTable();
        $this->addMessageColumns();
        $this->migrateDefaultOffer();
        $this->createPermissions();
    }

    public function down(): void
    {
        $this->deletePermissions();

        Schema::table('crm_client_messages', function (Blueprint $table): void {
            if (Schema::hasColumn('crm_client_messages', 'crm_workflow_offer_id')) {
                $table->dropForeign(['crm_workflow_offer_id']);
                $table->dropColumn('crm_workflow_offer_id');
            }

            if (Schema::hasColumn('crm_client_messages', 'crm_workflow_offer_label')) {
                $table->dropColumn('crm_workflow_offer_label');
            }
        });

        Schema::dropIfExists('crm_workflow_offers');
    }

    private function createOffersTable(): void
    {
        if (Schema::hasTable('crm_workflow_offers')) {
            return;
        }

        Schema::create('crm_workflow_offers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort']);
        });
    }

    private function addMessageColumns(): void
    {
        Schema::table('crm_client_messages', function (Blueprint $table): void {
            if (! Schema::hasColumn('crm_client_messages', 'crm_workflow_offer_id')) {
                $table->foreignUuid('crm_workflow_offer_id')
                    ->nullable()
                    ->after('default_offer_attached')
                    ->constrained('crm_workflow_offers')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('crm_client_messages', 'crm_workflow_offer_label')) {
                $table->string('crm_workflow_offer_label')
                    ->nullable()
                    ->after('crm_workflow_offer_id');
            }
        });
    }

    private function migrateDefaultOffer(): void
    {
        if (! Schema::hasTable('crm_workflow_settings') || ! Schema::hasTable('crm_workflow_offers')) {
            return;
        }

        $setting = DB::table('crm_workflow_settings')->where('id', 1)->first();

        if (! $setting || blank($setting->default_offer_path)) {
            return;
        }

        if (DB::table('crm_workflow_offers')->where('path', $setting->default_offer_path)->exists()) {
            return;
        }

        DB::table('crm_workflow_offers')->insert([
            'id' => (string) Str::uuid(),
            'label' => 'Domyślna oferta',
            'disk' => $setting->default_offer_disk ?: 'local',
            'path' => $setting->default_offer_path,
            'original_name' => $setting->default_offer_original_name,
            'is_active' => true,
            'sort' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
