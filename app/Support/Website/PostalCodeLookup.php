<?php

namespace App\Support\Website;

use App\Models\Website\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PostalCodeLookup
{
    private ?bool $hasPostalCodeTable = null;

    private ?bool $leadHasRegionColumns = null;

    /**
     * @var array<string, array{voivodeship: string, county: string, municipality: ?string, comment: ?string}|null>
     */
    private array $cache = [];

    public function normalize(?string $postalCode): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $postalCode);

        if (strlen($digits) !== 5) {
            return null;
        }

        return substr($digits, 0, 2).'-'.substr($digits, 2);
    }

    /**
     * @return array{voivodeship: string, county: string, municipality: ?string, comment: ?string}|null
     */
    public function find(?string $postalCode): ?array
    {
        $code = $this->normalize($postalCode);

        if (! $code || ! $this->hasPostalCodeTable()) {
            return null;
        }

        if (array_key_exists($code, $this->cache)) {
            return $this->cache[$code];
        }

        $record = DB::table('website_postal_codes')
            ->where('code', $code)
            ->first(['voivodeship', 'county', 'municipality', 'comment']);

        return $this->cache[$code] = $record ? [
            'voivodeship' => $record->voivodeship,
            'county' => $record->county,
            'municipality' => $record->municipality,
            'comment' => $record->comment,
        ] : null;
    }

    public function fillLeadRegion(Lead $lead, bool $onlyMissing = false): void
    {
        if (! $this->leadHasRegionColumns()) {
            return;
        }

        $code = $this->normalize($lead->postal_code);

        if (! $code) {
            if ($lead->isDirty('postal_code')) {
                $lead->postal_voivodeship = null;
                $lead->postal_county = null;
            }

            return;
        }

        $hasRegion = filled($lead->postal_voivodeship) && filled($lead->postal_county);

        if ($onlyMissing && $hasRegion) {
            return;
        }

        if (! $onlyMissing && ! $lead->isDirty('postal_code') && $hasRegion) {
            return;
        }

        $location = $this->find($code);

        $lead->postal_code = $code;
        $lead->postal_voivodeship = $location['voivodeship'] ?? null;
        $lead->postal_county = $location['county'] ?? null;
    }

    public function fillMissingLeadRegion(Lead $lead): Lead
    {
        if (! $this->leadHasRegionColumns()) {
            return $lead;
        }

        $original = [
            'postal_code' => $lead->postal_code,
            'postal_voivodeship' => $lead->postal_voivodeship,
            'postal_county' => $lead->postal_county,
        ];

        $this->fillLeadRegion($lead, onlyMissing: true);

        $changed = $lead->postal_code !== $original['postal_code']
            || $lead->postal_voivodeship !== $original['postal_voivodeship']
            || $lead->postal_county !== $original['postal_county'];

        if ($changed && $lead->exists) {
            $lead->saveQuietly();
        }

        return $lead;
    }

    public function postalLocationLabel(Lead $lead): ?string
    {
        $this->fillMissingLeadRegion($lead);

        if (blank($lead->postal_code)) {
            return null;
        }

        $region = collect([
            $lead->postal_voivodeship,
            $lead->postal_county,
        ])->filter()->implode(', ');

        return $lead->postal_code.($region ? " ({$region})" : '');
    }

    private function hasPostalCodeTable(): bool
    {
        return $this->hasPostalCodeTable ??= Schema::hasTable('website_postal_codes');
    }

    private function leadHasRegionColumns(): bool
    {
        return $this->leadHasRegionColumns ??= Schema::hasColumn('website_leads', 'postal_voivodeship')
            && Schema::hasColumn('website_leads', 'postal_county');
    }
}
