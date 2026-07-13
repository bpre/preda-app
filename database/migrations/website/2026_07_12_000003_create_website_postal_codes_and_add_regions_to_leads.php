<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_postal_codes', function (Blueprint $table): void {
            $table->string('code', 6)->primary();
            $table->string('voivodeship', 64);
            $table->string('county', 128);
            $table->string('municipality', 128)->nullable();
            $table->string('comment')->nullable();
        });

        $this->importPostalCodes();

        Schema::table('website_leads', function (Blueprint $table): void {
            $table->string('postal_voivodeship', 64)->nullable()->after('postal_code');
            $table->string('postal_county', 128)->nullable()->after('postal_voivodeship');
        });

        $this->backfillLeadPostalRegions();
    }

    public function down(): void
    {
        Schema::table('website_leads', function (Blueprint $table): void {
            $table->dropColumn([
                'postal_voivodeship',
                'postal_county',
            ]);
        });

        Schema::dropIfExists('website_postal_codes');
    }

    private function importPostalCodes(): void
    {
        $path = database_path('data/website_postal_codes.csv');

        if (! is_file($path) || ! ($handle = fopen($path, 'r'))) {
            return;
        }

        $header = fgetcsv($handle);
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $row = array_pad($row, 5, null);
            $record = array_combine($header, $row);

            if (! is_array($record) || blank($record['code'] ?? null)) {
                continue;
            }

            $code = $this->normalizePostalCode($record['code']);

            if (! $code) {
                continue;
            }

            $rows[] = [
                'code' => $code,
                'voivodeship' => trim((string) ($record['voivodeship'] ?? '')),
                'county' => trim((string) ($record['county'] ?? '')),
                'municipality' => filled($record['municipality'] ?? null) ? trim((string) $record['municipality']) : null,
                'comment' => filled($record['comment'] ?? null) ? trim((string) $record['comment']) : null,
            ];

            if (count($rows) >= 500) {
                DB::table('website_postal_codes')->insert($rows);
                $rows = [];
            }
        }

        fclose($handle);

        if ($rows !== []) {
            DB::table('website_postal_codes')->insert($rows);
        }
    }

    private function backfillLeadPostalRegions(): void
    {
        DB::table('website_leads')
            ->whereNotNull('postal_code')
            ->where(function ($query): void {
                $query
                    ->whereNull('postal_voivodeship')
                    ->orWhereNull('postal_county');
            })
            ->select(['id', 'postal_code'])
            ->orderBy('id')
            ->chunkById(200, function ($leads): void {
                $codes = $leads
                    ->map(fn (object $lead): ?string => $this->normalizePostalCode($lead->postal_code))
                    ->filter()
                    ->unique()
                    ->values();

                if ($codes->isEmpty()) {
                    return;
                }

                $locations = DB::table('website_postal_codes')
                    ->whereIn('code', $codes)
                    ->get(['code', 'voivodeship', 'county'])
                    ->keyBy('code');

                foreach ($leads as $lead) {
                    $code = $this->normalizePostalCode($lead->postal_code);
                    $location = $code ? $locations->get($code) : null;

                    if (! $location) {
                        continue;
                    }

                    DB::table('website_leads')
                        ->where('id', $lead->id)
                        ->update([
                            'postal_code' => $code,
                            'postal_voivodeship' => $location->voivodeship,
                            'postal_county' => $location->county,
                        ]);
                }
            });
    }

    private function normalizePostalCode(?string $postalCode): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $postalCode);

        if (strlen($digits) !== 5) {
            return null;
        }

        return substr($digits, 0, 2).'-'.substr($digits, 2);
    }
};
