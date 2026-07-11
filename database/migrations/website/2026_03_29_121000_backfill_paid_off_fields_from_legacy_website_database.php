<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        if (! Schema::hasTable('sentences')) {
            return;
        }

        $targetDatabase = $connection->getDatabaseName();
        $sourceDatabase = config('database.legacy_website_database', 'preda_website');

        if (! is_string($sourceDatabase) || $sourceDatabase === '' || $sourceDatabase === $targetDatabase) {
            return;
        }

        if (! preg_match('/^[A-Za-z0-9_]+$/', $sourceDatabase)) {
            return;
        }

        $sourceDatabaseExists = DB::selectOne(
            'SELECT EXISTS(SELECT 1 FROM information_schema.schemata WHERE schema_name = ?) AS `exists`',
            [$sourceDatabase],
        );

        if (! (bool) ($sourceDatabaseExists->exists ?? false)) {
            return;
        }

        $sourceTableExists = DB::selectOne(
            "SELECT EXISTS(
                SELECT 1
                FROM information_schema.tables
                WHERE table_schema = ?
                  AND table_name = 'sentences'
            ) AS `exists`",
            [$sourceDatabase],
        );

        if (! (bool) ($sourceTableExists->exists ?? false)) {
            return;
        }

        $sourceTable = sprintf('`%s`.`sentences`', $sourceDatabase);

        DB::statement("
            UPDATE `sentences` AS target
            INNER JOIN {$sourceTable} AS source
                ON source.`id` = target.`id`
               AND source.`slug` = target.`slug`
            SET target.`is_paid_off` = source.`is_paid_off`,
                target.`paid_off_year` = source.`paid_off_year`
            WHERE target.`is_paid_off` <> source.`is_paid_off`
               OR COALESCE(target.`paid_off_year`, '') <> COALESCE(source.`paid_off_year`, '')
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
