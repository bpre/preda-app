<?php

namespace App\Console\Commands;

use App\Services\LegacyDataImportService;
use Illuminate\Console\Command;
use Throwable;

class ImportLegacyData extends Command
{
    protected $signature = 'legacy:import-data
        {--dry-run : Show planned row counts without changing data}
        {--force : Import into the current target database}
        {--website-source= : Source database for the public website/CMS data}
        {--kancelaria-source= : Source database for kancelaria data}';

    protected $description = 'Imports data from local legacy website/CMS and kancelaria databases.';

    public function handle(LegacyDataImportService $importer): int
    {
        $websiteSource = $this->sourceOption('website-source', $importer->defaultWebsiteSource());
        $kancelariaSource = $this->sourceOption('kancelaria-source', $importer->defaultKancelariaSource());
        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && ! $this->option('force')) {
            $this->error('Import modifies the target database. Run with --force after making a backup.');

            return self::FAILURE;
        }

        if (! $dryRun && ! $importer->canRunDestructiveImport()) {
            foreach ($importer->availabilityErrors(forDestructiveImport: true) as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        try {
            $preview = $importer->preview($websiteSource, $kancelariaSource);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Target database: {$preview['target_schema']}");
        $this->info("Kancelaria source: {$preview['kancelaria_source']}");
        $this->info("Website/CMS source: {$preview['website_source']}");

        if ($preview['errors'] !== []) {
            foreach ($preview['errors'] as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->displayPreview($preview);

            return self::SUCCESS;
        }

        try {
            $result = $importer->import(
                websiteSource: $preview['website_source'],
                kancelariaSource: $preview['kancelaria_source'],
                logger: fn (string $message) => $this->line($message),
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->displayPreview($result['after'], 'Source rows');
        $this->info("Legacy data import completed. Imported tables: {$result['imported_tables']}. Imported rows: {$result['imported_rows']}.");

        return self::SUCCESS;
    }

    private function sourceOption(string $name, string $fallback): string
    {
        $value = trim((string) $this->option($name));

        return $value !== '' ? $value : $fallback;
    }

    private function displayPreview(array $preview, string $heading = 'Planned rows'): void
    {
        $this->table(
            ['Source', 'Target', $heading, 'Current target rows', 'Status'],
            array_map(
                fn (array $mapping): array => [
                    $mapping['source'],
                    $mapping['target'],
                    $mapping['source_rows'] ?? 'missing',
                    $mapping['target_rows'] ?? 'missing',
                    $mapping['status'],
                ],
                $preview['mappings'],
            ),
        );
    }
}
