<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuditLegacyFiles extends Command
{
    protected $signature = 'legacy:audit-files {--limit=10 : Number of missing path samples per source}';

    protected $description = 'Audits files referenced by imported legacy data against local storage disks.';

    public function handle(): int
    {
        $limit = max(0, (int) $this->option('limit'));
        $audits = [
            $this->auditJsonColumn('letters.files', 'letters', 'files', 'local', $limit),
            $this->auditJsonColumn('stages.files', 'stages', 'files', 'local', $limit),
            $this->auditStringColumn('offers.pdf_path', 'offers', 'pdf_path', 'local', $limit),
            $this->auditNeostampImages($limit),
            $this->auditJsonColumn('website_leads.files', 'website_leads', 'files', 'local', $limit),
            $this->auditJsonColumn('website_offers.files', 'website_offers', 'files', 'local', $limit),
            $this->auditJsonColumn('website_sentences.files', 'website_sentences', 'files', 'public', $limit),
            $this->auditJsonColumn('website_securities.files', 'website_securities', 'files', 'public', $limit),
        ];

        $this->table(
            ['Source', 'Disk', 'Records', 'Referenced files', 'Existing files', 'Missing files'],
            array_map(
                fn (array $audit): array => [
                    $audit['source'],
                    $audit['disk'],
                    $audit['records'],
                    $audit['referenced'],
                    $audit['existing'],
                    $audit['missing'],
                ],
                $audits,
            ),
        );

        foreach ($audits as $audit) {
            if ($audit['samples'] === []) {
                continue;
            }

            $this->warn("Missing samples for {$audit['source']}:");

            foreach ($audit['samples'] as $sample) {
                $this->line("  {$sample}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array{source: string, disk: string, records: int, referenced: int, existing: int, missing: int, samples: array<int, string>}
     */
    private function auditJsonColumn(string $source, string $table, string $column, string $disk, int $limit): array
    {
        $audit = $this->emptyAudit($source, $disk);

        DB::table($table)
            ->select(['id', $column])
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->where($column, '!=', '[]')
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$audit, $column, $disk, $limit): void {
                foreach ($rows as $row) {
                    $paths = $this->extractPaths($row->{$column});

                    if ($paths === []) {
                        continue;
                    }

                    $audit['records']++;

                    foreach ($paths as $path) {
                        $this->countPath($audit, $disk, $path, $limit);
                    }
                }
            });

        return $audit;
    }

    /**
     * @return array{source: string, disk: string, records: int, referenced: int, existing: int, missing: int, samples: array<int, string>}
     */
    private function auditNeostampImages(int $limit): array
    {
        $audit = $this->emptyAudit('neostamps.generated_file', 'local');

        DB::table('neostamps')
            ->select(['id', 'created_at', 'label'])
            ->whereNotNull('created_at')
            ->whereNotNull('label')
            ->where('label', '!=', '')
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$audit, $limit): void {
                foreach ($rows as $row) {
                    $date = substr((string) $row->created_at, 0, 10);
                    $label = trim((string) $row->label);

                    if ($date === '' || $label === '') {
                        continue;
                    }

                    $audit['records']++;
                    $this->countPath($audit, 'local', "neoznaczki/{$date}/{$label}_znaczek.jpg", $limit);
                }
            });

        return $audit;
    }

    /**
     * @return array{source: string, disk: string, records: int, referenced: int, existing: int, missing: int, samples: array<int, string>}
     */
    private function auditStringColumn(string $source, string $table, string $column, string $disk, int $limit): array
    {
        $audit = $this->emptyAudit($source, $disk);

        DB::table($table)
            ->select(['id', $column])
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$audit, $column, $disk, $limit): void {
                foreach ($rows as $row) {
                    $path = $this->normalizePath($row->{$column});

                    if ($path === null) {
                        continue;
                    }

                    $audit['records']++;
                    $this->countPath($audit, $disk, $path, $limit);
                }
            });

        return $audit;
    }

    /**
     * @return array{source: string, disk: string, records: int, referenced: int, existing: int, missing: int, samples: array<int, string>}
     */
    private function emptyAudit(string $source, string $disk): array
    {
        return [
            'source' => $source,
            'disk' => $disk,
            'records' => 0,
            'referenced' => 0,
            'existing' => 0,
            'missing' => 0,
            'samples' => [],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function extractPaths(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->extractPaths($decoded);
            }

            $path = $this->normalizePath($value);

            return $path === null ? [] : [$path];
        }

        if (! is_array($value)) {
            return [];
        }

        $paths = [];

        foreach ($value as $item) {
            array_push($paths, ...$this->extractPaths($item));
        }

        return array_values(array_unique($paths));
    }

    private function countPath(array &$audit, string $disk, string $path, int $limit): void
    {
        $audit['referenced']++;

        if (Storage::disk($disk)->exists($path)) {
            $audit['existing']++;

            return;
        }

        $audit['missing']++;

        if (count($audit['samples']) < $limit) {
            $audit['samples'][] = $path;
        }
    }

    private function normalizePath(mixed $path): ?string
    {
        if (! is_string($path)) {
            return null;
        }

        $path = trim($path);

        return $path === '' ? null : ltrim($path, '/');
    }
}
