<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const TEMPLATES = [
        [
            'action' => 'confirm_qualification',
            'name' => 'Potwierdzenie kwalifikacji sprawy',
            'subject' => 'Potwierdzenie kwalifikacji sprawy',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>potwierdzamy, że sprawa została zakwalifikowana do dalszej analizy przez kancelarię.</p>
<p>W kolejnym kroku skontaktujemy się w sprawie dalszych informacji oraz dokumentów potrzebnych do oceny możliwych działań.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 1,
        ],
        [
            'action' => 'request_additional_info',
            'name' => 'Prośba o dodatkowe informacje',
            'subject' => 'Prośba o dodatkowe informacje',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>do dalszej oceny sprawy potrzebujemy dodatkowych informacji.</p>
<p>Prosimy o przesłanie brakujących danych lub dokumentów, które pozwolą nam dokończyć analizę i wskazać możliwe dalsze kroki.</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 2,
        ],
        [
            'action' => 'send_contract_analysis',
            'name' => 'Analiza umowy',
            'subject' => 'Analiza umowy kredytu',
            'body' => <<<'HTML'
<p>Dzień dobry,</p>
<p>przesyłamy analizę przesłanej umowy kredytu.</p>
<p>[uzupełnij treść analizy przed wysyłką]</p>
<p>Z poważaniem,<br>Kancelaria Pręda</p>
HTML,
            'sort' => 3,
        ],
    ];

    private const PERMISSIONS = [
        'view_any_crm_mail_template',
        'view_crm_mail_template',
        'update_crm_mail_template',
    ];

    public function up(): void
    {
        Schema::create('crm_mail_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('action')->unique();
            $table->string('name');
            $table->string('subject');
            $table->longText('body');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort');
        });

        $now = now();

        DB::table('crm_mail_templates')->insert(array_map(
            fn (array $template): array => [
                'id' => (string) Str::uuid(),
                'action' => $template['action'],
                'name' => $template['name'],
                'subject' => $template['subject'],
                'body' => $template['body'],
                'is_active' => true,
                'sort' => $template['sort'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            self::TEMPLATES,
        ));

        $this->createPermissions();
    }

    public function down(): void
    {
        $this->deletePermissions();

        Schema::dropIfExists('crm_mail_templates');
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
    }
};
