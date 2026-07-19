<?php

namespace App\Services\Crm;

use App\Models\Website\Lead;
use App\Support\Crm\MarketingAgencyAccess;
use App\Support\Website\LeadStatuses;
use App\Support\Website\LeadTypes;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class LeadStatsService
{
    public const EXPORT_PERMISSION = 'export_lead_stats';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->can('ViewAny:Lead') === true
            || MarketingAgencyAccess::canViewLeadStats($user)
            || $user?->can(self::EXPORT_PERMISSION) === true;
    }

    public static function canExport(): bool
    {
        return auth()->user()?->can(self::EXPORT_PERMISSION) === true;
    }

    /**
     * @param  array<string, mixed>|null  $filters
     * @return array{
     *     filters: array{startDate: CarbonImmutable, endDate: CarbonImmutable, creditCurrency: string|null, leadType: string|null, leadSource: string|null},
     *     previousFilters: array{startDate: CarbonImmutable, endDate: CarbonImmutable, creditCurrency: string|null, leadType: string|null, leadSource: string|null},
     *     total: int,
     *     new: int,
     *     qualified: int,
     *     rejected: int,
     *     retained: int,
     *     previous: array{total: int, new: int, qualified: int, rejected: int, retained: int}
     * }
     */
    public function stats(?array $filters): array
    {
        $normalizedFilters = $this->normalizeFilters($filters ?? []);
        $previousFilters = $this->previousPeriodFilters($normalizedFilters);
        $currentCounts = $this->counts($normalizedFilters);
        $previousCounts = $this->counts($previousFilters);

        return [
            'filters' => $normalizedFilters,
            'previousFilters' => $previousFilters,
            'total' => $currentCounts['total'],
            'new' => $currentCounts['new'],
            'qualified' => $currentCounts['qualified'],
            'rejected' => $currentCounts['rejected'],
            'retained' => $currentCounts['retained'],
            'previous' => $previousCounts,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function leadSourceFilterOptions(): array
    {
        return [
            'all' => 'Wszystkie',
            ...self::leadSourceOptions(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function leadSourceOptions(): array
    {
        return [
            'google_ads' => 'Google Ads',
            'meta_ads' => 'Meta Ads',
            'remarketing' => 'Remarketing',
            'organic_search' => 'Wyszukiwarka organiczna',
            'referral' => 'Odesłanie z innej strony',
            'social' => 'Social media',
            'direct' => 'Wejście bezpośrednie',
            'other' => 'Inne źródło',
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{startDate: CarbonImmutable, endDate: CarbonImmutable, creditCurrency: string|null, leadType: string|null, leadSource: string|null}
     */
    public function normalizeFilters(array $filters): array
    {
        [$startDate, $endDate] = $this->dateRange($filters['leadDateRange'] ?? null);
        $creditCurrency = $filters['leadCurrency'] ?? null;
        $leadType = $filters['leadType'] ?? null;
        $leadSource = $filters['leadSource'] ?? null;
        $leadSource = is_string($leadSource) ? $leadSource : null;

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'creditCurrency' => in_array($creditCurrency, ['CHF', 'EUR'], true) ? $creditCurrency : null,
            'leadType' => LeadTypes::isValid(is_string($leadType) ? $leadType : null) ? $leadType : null,
            'leadSource' => $leadSource !== null && array_key_exists($leadSource, self::leadSourceOptions()) ? $leadSource : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<int, string|int>>
     */
    public function csvRows(array $filters): array
    {
        $stats = $this->stats($filters);
        $normalizedFilters = $stats['filters'];
        $total = $stats['total'];
        $context = $this->csvContextColumns($normalizedFilters);

        $rows = [
            [
                'Sekcja',
                'Wymiar',
                'Wartość',
                'Liczba',
                'Udział',
                'Zakres od',
                'Zakres do',
                'Filtr waluty',
                'Filtr typu leada',
                'Filtr źródła',
            ],
            ['Podsumowanie', 'Leady razem', '', $total, $this->percentage($total, $total), ...$context],
            ['Podsumowanie', 'Przed kwalifikacją', '', $stats['new'], $this->percentage($stats['new'], $total), ...$context],
            ['Podsumowanie', 'Zakwalifikowane', '', $stats['qualified'], $this->percentage($stats['qualified'], $total), ...$context],
            ['Podsumowanie', 'Odrzucone', '', $stats['rejected'], $this->percentage($stats['rejected'], $total), ...$context],
            ['Podsumowanie', 'Zlecone sprawy', '', $stats['retained'], $this->percentage($stats['retained'], $total), ...$context],
        ];

        foreach ($this->groupedRows($normalizedFilters, 'status', 'Status', LeadStatuses::options(), $total) as $row) {
            $rows[] = [...$row, ...$context];
        }

        foreach ($this->groupedRows($normalizedFilters, 'credit_currency', 'Waluta kredytu', [
            'CHF' => 'Kredyt CHF',
            'EUR' => 'Kredyt EUR',
        ], $total) as $row) {
            $rows[] = [...$row, ...$context];
        }

        foreach ($this->groupedRows($normalizedFilters, 'lead_type', 'Typ leada', LeadTypes::options(), $total) as $row) {
            $rows[] = [...$row, ...$context];
        }

        foreach ($this->groupedRows($normalizedFilters, 'attribution_channel', 'Źródło marketingowe', $this->attributionChannelLabels(), $total) as $row) {
            $rows[] = [...$row, ...$context];
        }

        foreach ($this->groupedRows($normalizedFilters, 'attribution_source', 'Źródło techniczne', [], $total) as $row) {
            $rows[] = [...$row, ...$context];
        }

        foreach ($this->groupedRows($normalizedFilters, 'attribution_medium', 'Medium', [], $total) as $row) {
            $rows[] = [...$row, ...$context];
        }

        foreach ($this->groupedRows($normalizedFilters, 'attribution_campaign', 'Kampania', [], $total) as $row) {
            $rows[] = [...$row, ...$context];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function filename(array $filters): string
    {
        $normalizedFilters = $this->normalizeFilters($filters);
        $parts = [
            'statystyki-leadow',
            $normalizedFilters['startDate']->format('Y-m-d'),
            $normalizedFilters['endDate']->format('Y-m-d'),
            $normalizedFilters['creditCurrency'] ?? 'wszystkie-waluty',
            $normalizedFilters['leadType'] ?? 'wszystkie-typy',
            $normalizedFilters['leadSource'] ?? 'wszystkie-zrodla',
        ];

        return Str::slug(implode('-', $parts)).'.csv';
    }

    /**
     * @param  array{startDate: CarbonImmutable, endDate: CarbonImmutable, creditCurrency: string|null, leadType: string|null, leadSource: string|null}  $filters
     */
    private function leadQuery(array $filters): Builder
    {
        return Lead::query()
            ->where('created_at', '>=', $filters['startDate']->startOfDay())
            ->where('created_at', '<=', $filters['endDate']->endOfDay())
            ->when($filters['creditCurrency'], fn (Builder $query, string $currency): Builder => $query->where('credit_currency', $currency))
            ->when($filters['leadType'], fn (Builder $query, string $type): Builder => $query->where('lead_type', $type))
            ->when($filters['leadSource'], fn (Builder $query, string $source): Builder => $query->where('attribution_channel', $source));
    }

    /**
     * @param  array{startDate: CarbonImmutable, endDate: CarbonImmutable, creditCurrency: string|null, leadType: string|null, leadSource: string|null}  $filters
     * @return array{total: int, new: int, qualified: int, rejected: int, retained: int}
     */
    private function counts(array $filters): array
    {
        return [
            'total' => $this->leadQuery($filters)->count(),
            'new' => $this->leadQuery($filters)
                ->where('status', LeadStatuses::NEW)
                ->count(),
            'qualified' => $this->leadQuery($filters)
                ->whereIn('status', [
                    LeadStatuses::QUALIFIED,
                    LeadStatuses::AUTOMATICALLY_QUALIFIED,
                ])
                ->count(),
            'rejected' => $this->leadQuery($filters)
                ->where('status', LeadStatuses::REJECTED)
                ->count(),
            'retained' => $this->leadQuery($filters)
                ->whereHas('potentialMatter', fn (Builder $query): Builder => $query->where('is_matter', true))
                ->count(),
        ];
    }

    /**
     * @param  array{startDate: CarbonImmutable, endDate: CarbonImmutable, creditCurrency: string|null, leadType: string|null, leadSource: string|null}  $filters
     * @return array{startDate: CarbonImmutable, endDate: CarbonImmutable, creditCurrency: string|null, leadType: string|null, leadSource: string|null}
     */
    private function previousPeriodFilters(array $filters): array
    {
        $startDate = $filters['startDate']->startOfDay();
        $endDate = $filters['endDate']->startOfDay();
        $days = max(1, ((int) floor($startDate->diffInDays($endDate))) + 1);
        $previousEndDate = $startDate->subDay()->endOfDay();
        $previousStartDate = $previousEndDate->subDays($days - 1)->startOfDay();

        return [
            ...$filters,
            'startDate' => $previousStartDate,
            'endDate' => $previousEndDate,
        ];
    }

    /**
     * @param  mixed  $range
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function dateRange(mixed $range): array
    {
        if (! is_string($range) || blank($range)) {
            return $this->defaultDateRange();
        }

        $range = trim($range);
        $presetRanges = $this->presetDateRanges();

        if (array_key_exists($range, $presetRanges)) {
            return $presetRanges[$range];
        }

        $parts = array_map('trim', explode(' - ', $range));

        if (count($parts) !== 2) {
            return $this->defaultDateRange();
        }

        try {
            return [
                CarbonImmutable::createFromFormat('Y-m-d', $parts[0])->startOfDay(),
                CarbonImmutable::createFromFormat('Y-m-d', $parts[1])->endOfDay(),
            ];
        } catch (\Throwable) {
            return $this->defaultDateRange();
        }
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function defaultDateRange(): array
    {
        return [
            CarbonImmutable::now()->subDays(29)->startOfDay(),
            CarbonImmutable::now()->endOfDay(),
        ];
    }

    /**
     * @return array<string, array{0: CarbonImmutable, 1: CarbonImmutable}>
     */
    private function presetDateRanges(): array
    {
        $now = CarbonImmutable::now();

        return [
            'Dzisiaj' => [$now->startOfDay(), $now->endOfDay()],
            'Ostatnie 7 dni' => [$now->subDays(6)->startOfDay(), $now->endOfDay()],
            'Ostatnie 30 dni' => [$now->subDays(29)->startOfDay(), $now->endOfDay()],
            'Ten miesiąc' => [$now->startOfMonth(), $now->endOfMonth()],
            'Poprzedni miesiąc' => [
                $now->subMonthNoOverflow()->startOfMonth(),
                $now->subMonthNoOverflow()->endOfMonth(),
            ],
            'Ten rok' => [$now->startOfYear(), $now->endOfYear()],
            'Poprzedni rok' => [
                $now->subYearNoOverflow()->startOfYear(),
                $now->subYearNoOverflow()->endOfYear(),
            ],
        ];
    }

    /**
     * @param  array{startDate: CarbonImmutable, endDate: CarbonImmutable, creditCurrency: string|null, leadType: string|null, leadSource: string|null}  $filters
     * @return array<int, string>
     */
    private function csvContextColumns(array $filters): array
    {
        return [
            $filters['startDate']->format('Y-m-d'),
            $filters['endDate']->format('Y-m-d'),
            $filters['creditCurrency'] ? 'Kredyt '.$filters['creditCurrency'] : 'Wszystkie',
            $filters['leadType'] ? (LeadTypes::label($filters['leadType']) ?? $filters['leadType']) : 'Wszystkie',
            $filters['leadSource'] ? (self::leadSourceOptions()[$filters['leadSource']] ?? $filters['leadSource']) : 'Wszystkie',
        ];
    }

    /**
     * @param  array{startDate: CarbonImmutable, endDate: CarbonImmutable, creditCurrency: string|null, leadType: string|null, leadSource: string|null}  $filters
     * @param  array<string, string>  $labels
     * @return array<int, array<int, string|int>>
     */
    private function groupedRows(array $filters, string $column, string $section, array $labels, int $total): array
    {
        $expression = "COALESCE(NULLIF({$column}, ''), '__empty__')";

        return $this->leadQuery($filters)
            ->selectRaw("{$expression} as value, COUNT(*) as aggregate")
            ->groupByRaw($expression)
            ->orderByDesc('aggregate')
            ->orderBy('value')
            ->get()
            ->map(fn (object $row): array => [
                $section,
                $section,
                $this->groupLabel((string) $row->value, $labels),
                (int) $row->aggregate,
                $this->percentage((int) $row->aggregate, $total),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function attributionChannelLabels(): array
    {
        return self::leadSourceOptions();
    }

    /**
     * @param  array<string, string>  $labels
     */
    private function groupLabel(string $value, array $labels): string
    {
        if ($value === '__empty__') {
            return 'Brak danych';
        }

        return $labels[$value] ?? $value;
    }

    private function percentage(int $value, int $total): string
    {
        if ($total === 0) {
            return '0%';
        }

        return number_format($value * 100 / $total, 1, ',', ' ').'%';
    }
}
