<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class ImportLegacyData extends Command
{
    protected $signature = 'legacy:import-data
        {--dry-run : Show planned row counts without changing data}
        {--force : Import into the current target database}
        {--website-source=preda.info : Source database for the public website}
        {--kancelaria-source=ewidencja.preda.info : Source database for kancelaria data}';

    protected $description = 'Imports data from the legacy preda.info and ewidencja.preda.info databases.';

    public function handle(PermissionRegistrar $permissionRegistrar): int
    {
        $targetSchema = DB::connection()->getDatabaseName();
        $websiteSource = (string) $this->option('website-source');
        $kancelariaSource = (string) $this->option('kancelaria-source');
        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && ! $this->option('force')) {
            $this->error('Import modifies the target database. Run with --force after making a backup.');

            return self::FAILURE;
        }

        if (in_array($targetSchema, [$websiteSource, $kancelariaSource], true)) {
            $this->error('Target database cannot be one of the source databases.');

            return self::FAILURE;
        }

        $mappings = [
            ...$this->kancelariaMappings($kancelariaSource),
            ...$this->websiteMappings($websiteSource),
        ];

        $errors = $this->validateMappings($targetSchema, $mappings);

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->info("Target database: {$targetSchema}");
        $this->info("Kancelaria source: {$kancelariaSource}");
        $this->info("Website source: {$websiteSource}");

        if ($dryRun) {
            $this->displayDryRun($targetSchema, $mappings);

            return self::SUCCESS;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($this->targetTables($mappings) as $table) {
                DB::statement('TRUNCATE TABLE '.$this->qualifiedTable($targetSchema, $table));
            }

            foreach ($mappings as $mapping) {
                $this->importTable($targetSchema, $mapping);
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->mergeWebsiteUserProfiles($targetSchema, $websiteSource);
        $this->mergeWebsiteAccess($targetSchema, $websiteSource);
        $this->ensurePanelAccess($targetSchema);
        $this->normalizeNullableForeignKeys($targetSchema);
        $this->repairBrokenOptionalRelations($targetSchema);
        $this->pruneInvalidPortalUsers($targetSchema);

        $permissionRegistrar->forgetCachedPermissions();

        $this->displayDryRun($targetSchema, $mappings, 'Source rows');
        $this->info('Legacy data import completed.');

        return self::SUCCESS;
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
     * @param  array<int, array{source_schema: string, source_table: string, target_table: string}>  $mappings
     * @return array<int, string>
     */
    private function validateMappings(string $targetSchema, array $mappings): array
    {
        $errors = [];

        foreach ($mappings as $mapping) {
            $sourceColumns = $this->columns($mapping['source_schema'], $mapping['source_table']);
            $targetColumns = $this->columns($targetSchema, $mapping['target_table']);

            if ($sourceColumns === []) {
                $errors[] = "Missing source table: {$mapping['source_schema']}.{$mapping['source_table']}";

                continue;
            }

            if ($targetColumns === []) {
                $errors[] = "Missing target table: {$targetSchema}.{$mapping['target_table']}";

                continue;
            }

            $missingInTarget = array_diff($sourceColumns, $targetColumns);

            foreach ($missingInTarget as $column) {
                $errors[] = "Missing target column: {$mapping['target_table']}.{$column}";
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
                    $errors[] = "Target column without source/default: {$mapping['target_table']}.{$column}";
                }
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
            ->get(['column_name', 'is_nullable', 'column_default', 'extra'])
            ->keyBy('COLUMN_NAME')
            ->all();
    }

    /**
     * @param  array{source_schema: string, source_table: string, target_table: string}  $mapping
     */
    private function importTable(string $targetSchema, array $mapping): void
    {
        $sourceColumns = $this->columns($mapping['source_schema'], $mapping['source_table']);
        $targetColumns = $this->columns($targetSchema, $mapping['target_table']);
        $columns = array_values(array_intersect($sourceColumns, $targetColumns));

        $targetColumnSql = implode(', ', array_map($this->quoteIdentifier(...), $columns));
        $selectSql = implode(', ', array_map(
            fn (string $column): string => $this->sourceColumnExpression($targetSchema, $mapping, $column),
            $columns,
        ));

        DB::statement(sprintf(
            'INSERT INTO %s (%s) SELECT %s FROM %s AS source_row',
            $this->qualifiedTable($targetSchema, $mapping['target_table']),
            $targetColumnSql,
            $selectSql,
            $this->qualifiedTable($mapping['source_schema'], $mapping['source_table']),
        ));

        $this->line("Imported {$mapping['source_schema']}.{$mapping['source_table']} -> {$mapping['target_table']}");
    }

    /**
     * @param  array{source_schema: string, source_table: string, target_table: string}  $mapping
     */
    private function sourceColumnExpression(string $targetSchema, array $mapping, string $column): string
    {
        if (
            $mapping['source_schema'] === (string) $this->option('website-source')
            && in_array($mapping['source_table'], ['posts', 'offices', 'lead_status_changes'], true)
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

    private function mergeWebsiteUserProfiles(string $targetSchema, string $websiteSource): void
    {
        DB::statement(sprintf(
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

    private function mergeWebsiteAccess(string $targetSchema, string $websiteSource): void
    {
        DB::statement(sprintf(
            'INSERT IGNORE INTO %s (name, guard_name, created_at, updated_at)
                SELECT name, guard_name, created_at, updated_at FROM %s',
            $this->qualifiedTable($targetSchema, 'roles'),
            $this->qualifiedTable($websiteSource, 'roles'),
        ));

        DB::statement(sprintf(
            'INSERT IGNORE INTO %s (name, guard_name, created_at, updated_at)
                SELECT name, guard_name, created_at, updated_at FROM %s',
            $this->qualifiedTable($targetSchema, 'permissions'),
            $this->qualifiedTable($websiteSource, 'permissions'),
        ));

        DB::statement(sprintf(
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

        DB::statement(sprintf(
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

        DB::statement(sprintf(
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
    }

    private function ensurePanelAccess(string $targetSchema): void
    {
        $now = now()->toDateTimeString();

        foreach (['access_kancelaria_panel', 'access_crm_panel', 'access_cms_panel'] as $permission) {
            DB::statement(sprintf(
                'INSERT IGNORE INTO %s (name, guard_name, created_at, updated_at) VALUES (?, ?, ?, ?)',
                $this->qualifiedTable($targetSchema, 'permissions'),
            ), [$permission, 'web', $now, $now]);
        }

        DB::statement(sprintf(
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
    }

    private function normalizeNullableForeignKeys(string $targetSchema): void
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
            ->get(['table_name', 'column_name']);

        foreach ($columns as $column) {
            $affected = DB::affectingStatement(sprintf(
                'UPDATE %s SET %s = NULL WHERE %s = ?',
                $this->qualifiedTable($targetSchema, $column->TABLE_NAME),
                $this->quoteIdentifier($column->COLUMN_NAME),
                $this->quoteIdentifier($column->COLUMN_NAME),
            ), ['']);

            if ($affected > 0) {
                $this->line("Normalized {$affected} empty {$column->TABLE_NAME}.{$column->COLUMN_NAME} values to NULL");
            }
        }
    }

    private function repairBrokenOptionalRelations(string $targetSchema): void
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

        if ($tasks > 0) {
            $this->line("Detached {$tasks} tasks from missing matters");
        }

        $contactLetters = DB::affectingStatement(sprintf(
            'DELETE contact_letter
                FROM %s AS contact_letter
                LEFT JOIN %s AS letter ON letter.id = contact_letter.letter_id
                WHERE letter.id IS NULL',
            $this->qualifiedTable($targetSchema, 'contact_letter'),
            $this->qualifiedTable($targetSchema, 'letters'),
        ));

        if ($contactLetters > 0) {
            $this->line("Deleted {$contactLetters} orphan contact_letter rows");
        }
    }

    private function pruneInvalidPortalUsers(string $targetSchema): void
    {
        $portalUsers = DB::affectingStatement(sprintf(
            'DELETE portal_user
                FROM %s AS portal_user
                LEFT JOIN %s AS contact ON contact.id = portal_user.contact_id
                WHERE portal_user.contact_id IS NOT NULL
                    AND contact.id IS NULL',
            $this->qualifiedTable($targetSchema, 'portal_users'),
            $this->qualifiedTable($targetSchema, 'contacts'),
        ));

        if ($portalUsers > 0) {
            $this->line("Deleted {$portalUsers} portal users linked to missing contacts");
        }
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

    /**
     * @param  array<int, array{source_schema: string, source_table: string, target_table: string}>  $mappings
     */
    private function displayDryRun(string $targetSchema, array $mappings, string $heading = 'Planned rows'): void
    {
        $rows = [];

        foreach ($mappings as $mapping) {
            $rows[] = [
                $mapping['source_schema'].'.'.$mapping['source_table'],
                $targetSchema.'.'.$mapping['target_table'],
                $this->countRows($mapping['source_schema'], $mapping['source_table']),
                $this->countRows($targetSchema, $mapping['target_table']),
            ];
        }

        $this->table(['Source', 'Target', $heading, 'Current target rows'], $rows);
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
