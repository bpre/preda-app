<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;

class LegacyDataImportService
{
    public function __construct(
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {}

    public function defaultWebsiteSource(): string
    {
        return (string) config('preda.legacy_import.website_source_database', 'preda_app');
    }

    public function defaultKancelariaSource(): string
    {
        return (string) config('preda.legacy_import.kancelaria_source_database', 'ewidencja');
    }

    public function targetSchema(): string
    {
        return DB::connection()->getDatabaseName();
    }

    /**
     * @return array<int, string>
     */
    public function availabilityErrors(bool $forDestructiveImport = false): array
    {
        $errors = [];

        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $errors[] = 'Import real data wymaga połączenia MySQL/MariaDB.';
        }

        if ($forDestructiveImport && ! app()->environment(['local', 'testing'])) {
            $errors[] = 'Odświeżanie real data jest dostępne tylko w środowisku lokalnym/testowym.';
        }

        return $errors;
    }

    public function canRunDestructiveImport(): bool
    {
        return $this->availabilityErrors(forDestructiveImport: true) === [];
    }

    /**
     * @return array{
     *     target_schema: string,
     *     website_source: string,
     *     kancelaria_source: string,
     *     mappings: array<int, array{
     *         source_schema: string,
     *         source_table: string,
     *         target_table: string,
     *         source: string,
     *         target: string,
     *         source_rows: int|null,
     *         target_rows: int|null,
     *         status: string
     *     }>,
     *     errors: array<int, string>
     * }
     */
    public function preview(?string $websiteSource = null, ?string $kancelariaSource = null): array
    {
        $this->ensureAvailable();

        $targetSchema = $this->targetSchema();
        $websiteSource = $this->normalizeSource($websiteSource, $this->defaultWebsiteSource());
        $kancelariaSource = $this->normalizeSource($kancelariaSource, $this->defaultKancelariaSource());
        $mappings = $this->mappings($websiteSource, $kancelariaSource);
        $errors = $this->sourceSelectionErrors($targetSchema, $websiteSource, $kancelariaSource);
        $rows = [];

        foreach ($mappings as $mapping) {
            $sourceColumns = $this->columns($mapping['source_schema'], $mapping['source_table']);
            $targetColumns = $this->columns($targetSchema, $mapping['target_table']);
            $status = 'OK';

            if ($sourceColumns === []) {
                $errors[] = "Brak tabeli źródłowej: {$mapping['source_schema']}.{$mapping['source_table']}";
                $status = 'Brak źródła';
            }

            if ($targetColumns === []) {
                $errors[] = "Brak tabeli docelowej: {$targetSchema}.{$mapping['target_table']}";
                $status = $status === 'OK' ? 'Brak celu' : $status.', brak celu';
            }

            if ($sourceColumns !== [] && $targetColumns !== []) {
                $tableErrors = $this->validateTableColumns($targetSchema, $mapping, $sourceColumns, $targetColumns);

                if ($tableErrors !== []) {
                    array_push($errors, ...$tableErrors);
                    $status = 'Różnice schematu';
                }
            }

            $rows[] = [
                ...$mapping,
                'source' => "{$mapping['source_schema']}.{$mapping['source_table']}",
                'target' => "{$targetSchema}.{$mapping['target_table']}",
                'source_rows' => $sourceColumns === [] ? null : $this->countRows($mapping['source_schema'], $mapping['source_table']),
                'target_rows' => $targetColumns === [] ? null : $this->countRows($targetSchema, $mapping['target_table']),
                'status' => $status,
            ];
        }

        foreach ($this->websiteSupportTables() as $table) {
            if ($this->columns($websiteSource, $table) === []) {
                $errors[] = "Brak pomocniczej tabeli źródłowej: {$websiteSource}.{$table}";
            }
        }

        return [
            'target_schema' => $targetSchema,
            'website_source' => $websiteSource,
            'kancelaria_source' => $kancelariaSource,
            'mappings' => $rows,
            'errors' => array_values(array_unique($errors)),
        ];
    }

    /**
     * @return array{
     *     before: array,
     *     after: array,
     *     log: array<int, string>,
     *     imported_tables: int,
     *     imported_rows: int,
     *     post_process: array<string, mixed>
     * }
     */
    public function import(?string $websiteSource = null, ?string $kancelariaSource = null, ?callable $logger = null): array
    {
        $this->ensureAvailable(forDestructiveImport: true);

        $before = $this->preview($websiteSource, $kancelariaSource);

        if ($before['errors'] !== []) {
            throw new RuntimeException('Import przerwany. Najpierw popraw błędy podglądu.');
        }

        $targetSchema = $before['target_schema'];
        $mappings = $this->mappings($before['website_source'], $before['kancelaria_source']);
        $log = [];
        $importedTables = 0;
        $importedRows = 0;

        $logMessage = function (string $message) use (&$log, $logger): void {
            $log[] = $message;

            if ($logger !== null) {
                $logger($message);
            }
        };

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($this->targetTables($mappings) as $table) {
                DB::statement('TRUNCATE TABLE '.$this->qualifiedTable($targetSchema, $table));
                $logMessage("Wyczyszczono {$targetSchema}.{$table}");
            }

            foreach ($mappings as $mapping) {
                $inserted = $this->importTable($targetSchema, $mapping);
                $importedTables++;
                $importedRows += $inserted;

                $logMessage("Zaimportowano {$mapping['source_schema']}.{$mapping['source_table']} -> {$mapping['target_table']} ({$inserted})");
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $postProcess = [
            'merged_website_user_profiles' => $this->mergeWebsiteUserProfiles($targetSchema, $before['website_source']),
            'merged_website_access' => $this->mergeWebsiteAccess($targetSchema, $before['website_source']),
            'ensured_panel_access' => $this->ensurePanelAccess($targetSchema),
            'normalized_nullable_foreign_keys' => $this->normalizeNullableForeignKeys($targetSchema),
            'repaired_optional_relations' => $this->repairBrokenOptionalRelations($targetSchema),
            'pruned_invalid_portal_users' => $this->pruneInvalidPortalUsers($targetSchema),
        ];

        $this->permissionRegistrar->forgetCachedPermissions();

        return [
            'before' => $before,
            'after' => $this->preview($before['website_source'], $before['kancelaria_source']),
            'log' => $log,
            'imported_tables' => $importedTables,
            'imported_rows' => $importedRows,
            'post_process' => $postProcess,
        ];
    }

    private function ensureAvailable(bool $forDestructiveImport = false): void
    {
        $errors = $this->availabilityErrors($forDestructiveImport);

        if ($errors !== []) {
            throw new RuntimeException(implode(' ', $errors));
        }
    }

    private function normalizeSource(?string $source, string $fallback): string
    {
        $source = trim((string) $source);

        return $source !== '' ? $source : $fallback;
    }

    /**
     * @return array<int, string>
     */
    private function sourceSelectionErrors(string $targetSchema, string $websiteSource, string $kancelariaSource): array
    {
        $errors = [];

        if (in_array($targetSchema, [$websiteSource, $kancelariaSource], true)) {
            $errors[] = 'Baza docelowa nie może być jedną z baz źródłowych.';
        }

        if ($websiteSource === $kancelariaSource) {
            $errors[] = 'Bazy źródłowe Kancelarii i strony/CMS nie mogą mieć tej samej nazwy.';
        }

        return $errors;
    }

    /**
     * @return array<int, array{source_schema: string, source_table: string, target_table: string}>
     */
    private function mappings(string $websiteSource, string $kancelariaSource): array
    {
        return [
            ...$this->kancelariaMappings($kancelariaSource),
            ...$this->websiteMappings($websiteSource),
        ];
    }

    /**
     * @return array<int, array{source_schema: string, source_table: string, target_table: string}>
     */
    private function kancelariaMappings(string $sourceSchema): array
    {
        return array_map(
            fn (string $table): array => [
                'source_schema' => $sourceSchema,
                'source_table' => $table,
                'target_table' => $table,
            ],
            [
                'users',
                'roles',
                'permissions',
                'role_has_permissions',
                'model_has_roles',
                'model_has_permissions',
                'activities',
                'branches',
                'contact_credit',
                'contact_deal',
                'contact_letter',
                'contact_matter',
                'contacts',
                'credit_deal',
                'credits',
                'deals',
                'departaments',
                'docs',
                'doctemplates',
                'exchange_rates',
                'expenses',
                'filament_comments',
                'filament_filter_set_user',
                'filament_filter_sets',
                'filament_filter_sets_managed_preset_views',
                'lawsuits',
                'letter_notification_templates',
                'letter_notifications',
                'letters',
                'matter_user',
                'matters',
                'neostamps',
                'notifications',
                'offers',
                'payments',
                'stages',
                'tasks',
                'template_stages',
            ],
        );
    }

    /**
     * @return array<int, array{source_schema: string, source_table: string, target_table: string}>
     */
    private function websiteMappings(string $sourceSchema): array
    {
        return [
            ['source_schema' => $sourceSchema, 'source_table' => 'banks', 'target_table' => 'website_banks'],
            ['source_schema' => $sourceSchema, 'source_table' => 'cities', 'target_table' => 'website_cities'],
            ['source_schema' => $sourceSchema, 'source_table' => 'contacts', 'target_table' => 'website_contacts'],
            ['source_schema' => $sourceSchema, 'source_table' => 'credits', 'target_table' => 'website_credits'],
            ['source_schema' => $sourceSchema, 'source_table' => 'faqs', 'target_table' => 'website_faqs'],
            ['source_schema' => $sourceSchema, 'source_table' => 'google_business_profile_connections', 'target_table' => 'website_google_business_profile_connections'],
            ['source_schema' => $sourceSchema, 'source_table' => 'leads', 'target_table' => 'website_leads'],
            ['source_schema' => $sourceSchema, 'source_table' => 'lead_status_changes', 'target_table' => 'website_lead_status_changes'],
            ['source_schema' => $sourceSchema, 'source_table' => 'offices', 'target_table' => 'website_offices'],
            ['source_schema' => $sourceSchema, 'source_table' => 'posts', 'target_table' => 'website_posts'],
            ['source_schema' => $sourceSchema, 'source_table' => 'reviews', 'target_table' => 'website_reviews'],
            ['source_schema' => $sourceSchema, 'source_table' => 'securities', 'target_table' => 'website_securities'],
            ['source_schema' => $sourceSchema, 'source_table' => 'sentence_content_templates', 'target_table' => 'website_sentence_content_templates'],
            ['source_schema' => $sourceSchema, 'source_table' => 'sentences', 'target_table' => 'website_sentences'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function websiteSupportTables(): array
    {
        return [
            'users',
            'roles',
            'permissions',
            'role_has_permissions',
            'model_has_roles',
            'model_has_permissions',
        ];
    }

    /**
     * @param  array{source_schema: string, source_table: string, target_table: string}  $mapping
     * @param  array<int, string>  $sourceColumns
     * @param  array<int, string>  $targetColumns
     * @return array<int, string>
     */
    private function validateTableColumns(string $targetSchema, array $mapping, array $sourceColumns, array $targetColumns): array
    {
        $errors = [];
        $missingInTarget = array_diff($sourceColumns, $targetColumns);

        foreach ($missingInTarget as $column) {
            $errors[] = "Brak kolumny docelowej: {$mapping['target_table']}.{$column}";
        }

        $targetMetadata = $this->columnMetadata($targetSchema, $mapping['target_table']);

        foreach (array_diff($targetColumns, $sourceColumns) as $column) {
            $metadata = $targetMetadata[$column] ?? null;

            if (
                $metadata !== null
                && $metadata->IS_NULLABLE === 'NO'
                && $metadata->COLUMN_DEFAULT === null
                && ! str_contains((string) $metadata->EXTRA, 'auto_increment')
                && ! str_contains((string) $metadata->EXTRA, 'GENERATED')
            ) {
                $errors[] = "Kolumna docelowa bez źródła/domyślnej wartości: {$mapping['target_table']}.{$column}";
            }
        }

        return $errors;
    }

    /**
     * @return array<int, string>
     */
    private function columns(string $schema, string $table): array
    {
        return DB::table('information_schema.columns')
            ->where('table_schema', $schema)
            ->where('table_name', $table)
            ->orderBy('ordinal_position')
            ->pluck('COLUMN_NAME')
            ->all();
    }

    /**
     * @return array<string, object>
     */
    private function columnMetadata(string $schema, string $table): array
    {
        return DB::table('information_schema.columns')
            ->where('table_schema', $schema)
            ->where('table_name', $table)
            ->get(['COLUMN_NAME', 'IS_NULLABLE', 'COLUMN_DEFAULT', 'EXTRA'])
            ->keyBy('COLUMN_NAME')
            ->all();
    }

    /**
     * @param  array{source_schema: string, source_table: string, target_table: string}  $mapping
     */
    private function importTable(string $targetSchema, array $mapping): int
    {
        $sourceColumns = $this->columns($mapping['source_schema'], $mapping['source_table']);
        $targetColumns = $this->columns($targetSchema, $mapping['target_table']);
        $columns = array_values(array_intersect($sourceColumns, $targetColumns));

        $targetColumnSql = implode(', ', array_map($this->quoteIdentifier(...), $columns));
        $selectSql = implode(', ', array_map(
            fn (string $column): string => $this->sourceColumnExpression($targetSchema, $mapping, $column),
            $columns,
        ));

        return DB::affectingStatement(sprintf(
            'INSERT INTO %s (%s) SELECT %s FROM %s AS source_row',
            $this->qualifiedTable($targetSchema, $mapping['target_table']),
            $targetColumnSql,
            $selectSql,
            $this->qualifiedTable($mapping['source_schema'], $mapping['source_table']),
        ));
    }

    /**
     * @param  array{source_schema: string, source_table: string, target_table: string}  $mapping
     */
    private function sourceColumnExpression(string $targetSchema, array $mapping, string $column): string
    {
        if (
            in_array($mapping['target_table'], ['website_posts', 'website_offices', 'website_lead_status_changes'], true)
            && in_array($column, ['author_id', 'director_id', 'changed_by'], true)
        ) {
            return sprintf(
                '(SELECT target_user.id FROM %s AS source_user JOIN %s AS target_user ON target_user.email = source_user.email WHERE source_user.id = source_row.%s LIMIT 1) AS %s',
                $this->qualifiedTable($mapping['source_schema'], 'users'),
                $this->qualifiedTable($targetSchema, 'users'),
                $this->quoteIdentifier($column),
                $this->quoteIdentifier($column),
            );
        }

        return 'source_row.'.$this->quoteIdentifier($column);
    }

    private function mergeWebsiteUserProfiles(string $targetSchema, string $websiteSource): int
    {
        return DB::affectingStatement(sprintf(
            'UPDATE %s AS target_user
                JOIN %s AS source_user ON source_user.email = target_user.email
                SET target_user.website_title = source_user.website_title,
                    target_user.website_description = source_user.website_description,
                    target_user.website_is_published = source_user.website_is_published,
                    target_user.website_sort = source_user.website_sort',
            $this->qualifiedTable($targetSchema, 'users'),
            $this->qualifiedTable($websiteSource, 'users'),
        ));
    }

    /**
     * @return array<string, int>
     */
    private function mergeWebsiteAccess(string $targetSchema, string $websiteSource): array
    {
        $roles = DB::affectingStatement(sprintf(
            'INSERT IGNORE INTO %s (name, guard_name, created_at, updated_at)
                SELECT name, guard_name, created_at, updated_at FROM %s',
            $this->qualifiedTable($targetSchema, 'roles'),
            $this->qualifiedTable($websiteSource, 'roles'),
        ));

        $permissions = DB::affectingStatement(sprintf(
            'INSERT IGNORE INTO %s (name, guard_name, created_at, updated_at)
                SELECT name, guard_name, created_at, updated_at FROM %s',
            $this->qualifiedTable($targetSchema, 'permissions'),
            $this->qualifiedTable($websiteSource, 'permissions'),
        ));

        $rolePermissions = DB::affectingStatement(sprintf(
            'INSERT IGNORE INTO %s (permission_id, role_id)
                SELECT target_permission.id, target_role.id
                FROM %s AS source_role_permission
                JOIN %s AS source_permission ON source_permission.id = source_role_permission.permission_id
                JOIN %s AS source_role ON source_role.id = source_role_permission.role_id
                JOIN %s AS target_permission ON target_permission.name = source_permission.name
                    AND target_permission.guard_name = source_permission.guard_name
                JOIN %s AS target_role ON target_role.name = source_role.name
                    AND target_role.guard_name = source_role.guard_name',
            $this->qualifiedTable($targetSchema, 'role_has_permissions'),
            $this->qualifiedTable($websiteSource, 'role_has_permissions'),
            $this->qualifiedTable($websiteSource, 'permissions'),
            $this->qualifiedTable($websiteSource, 'roles'),
            $this->qualifiedTable($targetSchema, 'permissions'),
            $this->qualifiedTable($targetSchema, 'roles'),
        ));

        $modelRoles = DB::affectingStatement(sprintf(
            'INSERT IGNORE INTO %s (role_id, model_type, model_id)
                SELECT target_role.id, source_model_role.model_type, target_user.id
                FROM %s AS source_model_role
                JOIN %s AS source_role ON source_role.id = source_model_role.role_id
                JOIN %s AS source_user ON source_user.id = source_model_role.model_id
                JOIN %s AS target_user ON target_user.email = source_user.email
                JOIN %s AS target_role ON target_role.name = source_role.name
                    AND target_role.guard_name = source_role.guard_name
                WHERE source_model_role.model_type = ?',
            $this->qualifiedTable($targetSchema, 'model_has_roles'),
            $this->qualifiedTable($websiteSource, 'model_has_roles'),
            $this->qualifiedTable($websiteSource, 'roles'),
            $this->qualifiedTable($websiteSource, 'users'),
            $this->qualifiedTable($targetSchema, 'users'),
            $this->qualifiedTable($targetSchema, 'roles'),
        ), [User::class]);

        $modelPermissions = DB::affectingStatement(sprintf(
            'INSERT IGNORE INTO %s (permission_id, model_type, model_id)
                SELECT target_permission.id, source_model_permission.model_type, target_user.id
                FROM %s AS source_model_permission
                JOIN %s AS source_permission ON source_permission.id = source_model_permission.permission_id
                JOIN %s AS source_user ON source_user.id = source_model_permission.model_id
                JOIN %s AS target_user ON target_user.email = source_user.email
                JOIN %s AS target_permission ON target_permission.name = source_permission.name
                    AND target_permission.guard_name = source_permission.guard_name
                WHERE source_model_permission.model_type = ?',
            $this->qualifiedTable($targetSchema, 'model_has_permissions'),
            $this->qualifiedTable($websiteSource, 'model_has_permissions'),
            $this->qualifiedTable($websiteSource, 'permissions'),
            $this->qualifiedTable($websiteSource, 'users'),
            $this->qualifiedTable($targetSchema, 'users'),
            $this->qualifiedTable($targetSchema, 'permissions'),
        ), [User::class]);

        return [
            'roles' => $roles,
            'permissions' => $permissions,
            'role_has_permissions' => $rolePermissions,
            'model_has_roles' => $modelRoles,
            'model_has_permissions' => $modelPermissions,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function ensurePanelAccess(string $targetSchema): array
    {
        $now = now()->toDateTimeString();
        $permissions = 0;

        foreach (['access_kancelaria_panel', 'access_crm_panel', 'access_cms_panel'] as $permission) {
            $permissions += DB::affectingStatement(sprintf(
                'INSERT IGNORE INTO %s (name, guard_name, created_at, updated_at) VALUES (?, ?, ?, ?)',
                $this->qualifiedTable($targetSchema, 'permissions'),
            ), [$permission, 'web', $now, $now]);
        }

        $modelPermissions = DB::affectingStatement(sprintf(
            'INSERT IGNORE INTO %s (permission_id, model_type, model_id)
                SELECT permission.id, ?, user.id
                FROM %s AS user
                CROSS JOIN %s AS permission
                WHERE user.is_active = 1
                    AND user.is_employee = 1
                    AND permission.guard_name = ?
                    AND permission.name IN (?, ?, ?)',
            $this->qualifiedTable($targetSchema, 'model_has_permissions'),
            $this->qualifiedTable($targetSchema, 'users'),
            $this->qualifiedTable($targetSchema, 'permissions'),
        ), [
            User::class,
            'web',
            'access_kancelaria_panel',
            'access_crm_panel',
            'access_cms_panel',
        ]);

        return [
            'permissions' => $permissions,
            'model_has_permissions' => $modelPermissions,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function normalizeNullableForeignKeys(string $targetSchema): array
    {
        $columns = DB::table('information_schema.columns')
            ->where('table_schema', $targetSchema)
            ->where('is_nullable', 'YES')
            ->whereIn('data_type', ['char', 'varchar'])
            ->where(function ($query): void {
                $query
                    ->where('column_name', 'like', '%\_id')
                    ->orWhereIn('column_name', [
                        'lawfirm_id',
                        'sender_id',
                    ]);
            })
            ->orderBy('table_name')
            ->orderBy('ordinal_position')
            ->get(['TABLE_NAME', 'COLUMN_NAME']);

        $affectedRows = [];

        foreach ($columns as $column) {
            $affected = DB::affectingStatement(sprintf(
                'UPDATE %s SET %s = NULL WHERE %s = ?',
                $this->qualifiedTable($targetSchema, $column->TABLE_NAME),
                $this->quoteIdentifier($column->COLUMN_NAME),
                $this->quoteIdentifier($column->COLUMN_NAME),
            ), ['']);

            if ($affected > 0) {
                $affectedRows["{$column->TABLE_NAME}.{$column->COLUMN_NAME}"] = $affected;
            }
        }

        return $affectedRows;
    }

    /**
     * @return array<string, int>
     */
    private function repairBrokenOptionalRelations(string $targetSchema): array
    {
        $tasks = DB::affectingStatement(sprintf(
            'UPDATE %s AS task
                LEFT JOIN %s AS matter ON matter.id = task.matter_id
                SET task.matter_id = NULL
                WHERE task.matter_id IS NOT NULL
                    AND matter.id IS NULL',
            $this->qualifiedTable($targetSchema, 'tasks'),
            $this->qualifiedTable($targetSchema, 'matters'),
        ));

        $contactLetters = DB::affectingStatement(sprintf(
            'DELETE contact_letter
                FROM %s AS contact_letter
                LEFT JOIN %s AS letter ON letter.id = contact_letter.letter_id
                WHERE letter.id IS NULL',
            $this->qualifiedTable($targetSchema, 'contact_letter'),
            $this->qualifiedTable($targetSchema, 'letters'),
        ));

        return [
            'tasks_detached_from_missing_matters' => $tasks,
            'orphan_contact_letter_rows_deleted' => $contactLetters,
        ];
    }

    private function pruneInvalidPortalUsers(string $targetSchema): int
    {
        return DB::affectingStatement(sprintf(
            'DELETE portal_user
                FROM %s AS portal_user
                LEFT JOIN %s AS contact ON contact.id = portal_user.contact_id
                WHERE portal_user.contact_id IS NOT NULL
                    AND contact.id IS NULL',
            $this->qualifiedTable($targetSchema, 'portal_users'),
            $this->qualifiedTable($targetSchema, 'contacts'),
        ));
    }

    /**
     * @param  array<int, array{target_table: string}>  $mappings
     * @return array<int, string>
     */
    private function targetTables(array $mappings): array
    {
        return array_values(array_unique(array_map(
            fn (array $mapping): string => $mapping['target_table'],
            $mappings,
        )));
    }

    private function countRows(string $schema, string $table): int
    {
        $result = DB::selectOne(sprintf(
            'SELECT COUNT(*) AS aggregate FROM %s',
            $this->qualifiedTable($schema, $table),
        ));

        return (int) $result->aggregate;
    }

    private function qualifiedTable(string $schema, string $table): string
    {
        return $this->quoteIdentifier($schema).'.'.$this->quoteIdentifier($table);
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }
}
