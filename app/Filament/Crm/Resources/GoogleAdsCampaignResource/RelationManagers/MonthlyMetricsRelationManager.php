<?php

namespace App\Filament\Crm\Resources\GoogleAdsCampaignResource\RelationManagers;

use App\Filament\Crm\Resources\GoogleAdsCampaignResource;
use App\Models\Website\GoogleAdsCampaignMonthlyMetric;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MonthlyMetricsRelationManager extends RelationManager
{
    protected static string $relationship = 'monthlyMetrics';

    protected static ?string $title = 'Wydatki miesięczne';

    protected static ?string $modelLabel = 'Miesięczne metryki kampanii';

    protected static ?string $pluralModelLabel = 'Miesięczne metryki kampanii';

    protected static bool $isLazy = false;

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return GoogleAdsCampaignResource::canView($ownerRecord);
    }

    public function table(Table $table): Table
    {
        $currency = $this->getOwnerRecord()->currency_code ?: 'PLN';

        return $table
            ->recordTitleAttribute('month')
            ->modifyQueryUsing(fn (Builder $query): Builder => $this->withCrmLeadMetrics($query))
            ->columns([
                TextColumn::make('month')
                    ->label('Miesiąc')
                    ->date('Y-m')
                    ->weight(FontWeight::Medium)
                    ->sortable(),
                TextColumn::make('cost_micros')
                    ->label('Koszt')
                    ->formatStateUsing(fn (mixed $state, GoogleAdsCampaignMonthlyMetric $record): string => self::moneyMicros($state, $record->currency_code))
                    ->alignEnd()
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('Razem')
                            ->formatStateUsing(fn (mixed $state): string => self::moneyMicros($state, $currency)),
                    ]),
                TextColumn::make('clicks')
                    ->label('Kliknięcia')
                    ->numeric(locale: 'pl')
                    ->alignEnd()
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('Razem')
                            ->numeric(locale: 'pl'),
                    ]),
                TextColumn::make('conversions')
                    ->label('Konwersje')
                    ->numeric(decimalPlaces: 2, locale: 'pl')
                    ->alignEnd()
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('Razem')
                            ->numeric(decimalPlaces: 2, locale: 'pl'),
                    ]),
                TextColumn::make('crm_leads_count')
                    ->label('Leady CRM')
                    ->state(fn (GoogleAdsCampaignMonthlyMetric $record): int => (int) ($record->getAttribute('crm_leads_count') ?? 0))
                    ->numeric(locale: 'pl')
                    ->alignEnd()
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $this->sortByCrmLeadsCount($query, $direction)),
                TextColumn::make('cost_per_crm_lead')
                    ->label('Koszt / lead')
                    ->state(fn (GoogleAdsCampaignMonthlyMetric $record): ?float => $record->getAttribute('cost_per_crm_lead'))
                    ->formatStateUsing(fn (mixed $state, GoogleAdsCampaignMonthlyMetric $record): string => self::money($state, $record->currency_code))
                    ->placeholder('-')
                    ->alignEnd()
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $this->sortByCostPerCrmLead($query, $direction)),
                TextColumn::make('impressions')
                    ->label('Wyświetlenia')
                    ->numeric(locale: 'pl')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('average_cpc_micros')
                    ->label('Śr. CPC')
                    ->formatStateUsing(fn (mixed $state, GoogleAdsCampaignMonthlyMetric $record): string => self::moneyMicros($state, $record->currency_code))
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('month', 'desc')
            ->paginated([12, 24, 48, 96])
            ->defaultPaginationPageOption(12)
            ->striped()
            ->emptyStateHeading('Brak danych miesięcznych')
            ->emptyStateDescription('Użyj synchronizacji Google Ads, aby pobrać wydatki kampanii.');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    private function withCrmLeadMetrics(Builder $query): Builder
    {
        $crmLeadsCountSql = $this->crmLeadsCountSql($query);

        return $query
            ->select('website_google_ads_campaign_monthly_metrics.*')
            ->selectRaw("{$crmLeadsCountSql} as crm_leads_count", [$this->ownerCampaignId()])
            ->selectRaw(
                "CASE WHEN {$crmLeadsCountSql} > 0 THEN (website_google_ads_campaign_monthly_metrics.cost_micros / 1000000) / {$crmLeadsCountSql} ELSE NULL END as cost_per_crm_lead",
                [$this->ownerCampaignId(), $this->ownerCampaignId()],
            );
    }

    private function sortByCrmLeadsCount(Builder $query, string $direction): Builder
    {
        return $query->orderByRaw($this->crmLeadsCountSql($query).' '.$this->sortDirection($direction), [
            $this->ownerCampaignId(),
        ]);
    }

    private function sortByCostPerCrmLead(Builder $query, string $direction): Builder
    {
        $crmLeadsCountSql = $this->crmLeadsCountSql($query);

        return $query->orderByRaw(
            "CASE WHEN {$crmLeadsCountSql} > 0 THEN (website_google_ads_campaign_monthly_metrics.cost_micros / 1000000) / {$crmLeadsCountSql} ELSE NULL END ".$this->sortDirection($direction),
            [$this->ownerCampaignId(), $this->ownerCampaignId()],
        );
    }

    private function crmLeadsCountSql(Builder $query): string
    {
        $leadMonthSql = $this->monthSql($query, 'website_leads.created_at');
        $metricMonthSql = $this->monthSql($query, 'website_google_ads_campaign_monthly_metrics.month');

        return <<<SQL
            (
                SELECT COUNT(*)
                FROM website_leads
                WHERE website_leads.google_ads_campaign_id = ?
                    AND {$leadMonthSql} = {$metricMonthSql}
            )
            SQL;
    }

    private function monthSql(Builder $query, string $column): string
    {
        return match ($query->getConnection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m-01', {$column})",
            default => "DATE_FORMAT({$column}, '%Y-%m-01')",
        };
    }

    private function ownerCampaignId(): string
    {
        return (string) $this->getOwnerRecord()->campaign_id;
    }

    private function sortDirection(string $direction): string
    {
        return $direction === 'desc' ? 'desc' : 'asc';
    }

    private static function moneyMicros(mixed $value, ?string $currency): string
    {
        if (! is_numeric($value)) {
            return '-';
        }

        return self::money(((float) $value) / 1_000_000, $currency);
    }

    private static function money(mixed $value, ?string $currency): string
    {
        if (! is_numeric($value)) {
            return '-';
        }

        return number_format((float) $value, 2, ',', ' ').' '.($currency ?: 'PLN');
    }
}
