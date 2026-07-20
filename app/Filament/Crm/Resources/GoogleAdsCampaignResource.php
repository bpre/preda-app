<?php

namespace App\Filament\Crm\Resources;

use App\Filament\Crm\Resources\GoogleAdsCampaignResource\Pages\ListGoogleAdsCampaigns;
use App\Filament\Crm\Resources\GoogleAdsCampaignResource\Pages\ViewGoogleAdsCampaign;
use App\Filament\Crm\Resources\GoogleAdsCampaignResource\RelationManagers\MonthlyMetricsRelationManager;
use App\Models\Website\GoogleAdsCampaign;
use App\Services\Crm\LeadStatsService;
use App\Support\Crm\MarketingAgencyAccess;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GoogleAdsCampaignResource extends Resource
{
    protected static ?string $model = GoogleAdsCampaign::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'kampanie-google-ads';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Kampanie Google Ads';

    protected static ?string $modelLabel = 'Kampania Google Ads';

    protected static ?string $pluralModelLabel = 'Kampanie Google Ads';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function canViewAny(): bool
    {
        return LeadStatsService::canView()
            || MarketingAgencyAccess::canViewMarketingLeads();
    }

    public static function canView(Model $record): bool
    {
        return self::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('monthlyMetrics')
            ->withCount('leads');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kampania')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nazwa')
                            ->weight(FontWeight::Bold)
                            ->columnSpanFull(),
                        TextEntry::make('campaign_id')
                            ->label('ID kampanii'),
                        TextEntry::make('customer_id')
                            ->label('ID klienta Google Ads'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => self::statusLabel($state))
                            ->color(fn (?string $state): string => self::statusColor($state))
                            ->placeholder('-'),
                        TextEntry::make('advertising_channel_type')
                            ->label('Typ')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => self::channelTypeLabel($state))
                            ->placeholder('-'),
                        TextEntry::make('bidding_strategy_type')
                            ->label('Strategia stawek')
                            ->formatStateUsing(fn (?string $state): string => self::biddingStrategyLabel($state))
                            ->placeholder('-'),
                        TextEntry::make('budget_amount_micros')
                            ->label('Budżet')
                            ->formatStateUsing(fn (mixed $state, GoogleAdsCampaign $record): string => self::moneyMicros($state, $record->currency_code))
                            ->placeholder('-'),
                    ])
                    ->columns(3),
                Section::make('Wyniki')
                    ->schema([
                        TextEntry::make('clicks')
                            ->label('Kliknięcia')
                            ->numeric(),
                        TextEntry::make('impressions')
                            ->label('Wyświetlenia')
                            ->numeric(),
                        TextEntry::make('ctr')
                            ->label('CTR')
                            ->formatStateUsing(fn (mixed $state): string => self::percentage($state)),
                        TextEntry::make('average_cpc_micros')
                            ->label('Śr. CPC')
                            ->formatStateUsing(fn (mixed $state, GoogleAdsCampaign $record): string => self::moneyMicros($state, $record->currency_code)),
                        TextEntry::make('cost_micros')
                            ->label('Koszt')
                            ->formatStateUsing(fn (mixed $state, GoogleAdsCampaign $record): string => self::moneyMicros($state, $record->currency_code)),
                        TextEntry::make('conversions')
                            ->label('Konwersje')
                            ->formatStateUsing(fn (mixed $state): string => self::number($state, 2)),
                        TextEntry::make('leads_count')
                            ->label('Leady')
                            ->numeric(),
                        TextEntry::make('cost_per_lead')
                            ->label('Koszt / lead')
                            ->formatStateUsing(fn (mixed $state, GoogleAdsCampaign $record): string => self::money($state, $record->currency_code)),
                        TextEntry::make('metrics_from')
                            ->label('Metryki od')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('metrics_to')
                            ->label('Metryki do')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('last_synced_at')
                            ->label('Ostatnia synchronizacja')
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Kampania')
                    ->description(fn (GoogleAdsCampaign $record): string => 'ID: '.$record->campaign_id)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::statusLabel($state))
                    ->color(fn (?string $state): string => self::statusColor($state))
                    ->sortable(),
                TextColumn::make('advertising_channel_type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::channelTypeLabel($state))
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('clicks')
                    ->label('Kliknięcia')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('impressions')
                    ->label('Wyświetlenia')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('ctr')
                    ->label('CTR')
                    ->formatStateUsing(fn (mixed $state): string => self::percentage($state))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('cost_micros')
                    ->label('Koszt')
                    ->formatStateUsing(fn (mixed $state, GoogleAdsCampaign $record): string => self::moneyMicros($state, $record->currency_code))
                    ->sortable(),
                TextColumn::make('average_cpc_micros')
                    ->label('Śr. CPC')
                    ->formatStateUsing(fn (mixed $state, GoogleAdsCampaign $record): string => self::moneyMicros($state, $record->currency_code))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('conversions')
                    ->label('Konwersje')
                    ->formatStateUsing(fn (mixed $state): string => self::number($state, 2))
                    ->sortable(),
                TextColumn::make('leads_count')
                    ->label('Leady')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cost_per_lead')
                    ->label('Koszt / lead')
                    ->formatStateUsing(fn (mixed $state, GoogleAdsCampaign $record): string => self::money($state, $record->currency_code)),
                TextColumn::make('last_synced_at')
                    ->label('Ostatnia synchronizacja')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(fn (): array => self::statusOptions()),
                SelectFilter::make('advertising_channel_type')
                    ->label('Typ kampanii')
                    ->options(fn (): array => GoogleAdsCampaign::query()
                        ->whereNotNull('advertising_channel_type')
                        ->distinct()
                        ->orderBy('advertising_channel_type')
                        ->pluck('advertising_channel_type')
                        ->mapWithKeys(fn (string $type): array => [$type => self::channelTypeLabel($type)])
                        ->all()),
            ])
            ->defaultSort('last_synced_at', 'desc')
            ->recordUrl(fn (GoogleAdsCampaign $record): string => self::getUrl('view', ['record' => $record], panel: 'crm'));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGoogleAdsCampaigns::route('/'),
            'view' => ViewGoogleAdsCampaign::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            MonthlyMetricsRelationManager::class,
        ];
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

    private static function percentage(mixed $value): string
    {
        if (! is_numeric($value)) {
            return '-';
        }

        return number_format(((float) $value) * 100, 2, ',', ' ').'%';
    }

    private static function number(mixed $value, int $decimals = 0): string
    {
        if (! is_numeric($value)) {
            return '-';
        }

        return number_format((float) $value, $decimals, ',', ' ');
    }

    private static function statusOptions(): array
    {
        return [
            'ENABLED' => self::statusLabel('ENABLED'),
            'PAUSED' => self::statusLabel('PAUSED'),
            'REMOVED' => self::statusLabel('REMOVED'),
        ];
    }

    private static function statusLabel(?string $status): string
    {
        return match ($status) {
            'ENABLED' => 'Aktywna',
            'PAUSED' => 'Wstrzymana',
            'REMOVED' => 'Usunięta',
            default => $status ?: '-',
        };
    }

    private static function statusColor(?string $status): string
    {
        return match ($status) {
            'ENABLED' => 'success',
            'PAUSED' => 'warning',
            'REMOVED' => 'danger',
            default => 'gray',
        };
    }

    private static function channelTypeLabel(?string $type): string
    {
        return match ($type) {
            'SEARCH' => 'Sieć wyszukiwania',
            'DISPLAY' => 'Sieć reklamowa',
            'SHOPPING' => 'Zakupy',
            'VIDEO' => 'Wideo',
            'MULTI_CHANNEL' => 'Wiele kanałów',
            'HOTEL' => 'Hotele',
            'LOCAL' => 'Lokalna',
            'SMART' => 'Inteligentna',
            'PERFORMANCE_MAX' => 'Performance Max',
            'LOCAL_SERVICES' => 'Usługi lokalne',
            'DISCOVERY' => 'Discovery',
            'DEMAND_GEN' => 'Generowanie popytu',
            'TRAVEL' => 'Podróże',
            default => $type ?: '-',
        };
    }

    private static function biddingStrategyLabel(?string $type): string
    {
        return match ($type) {
            'COMMISSION' => 'Prowizja',
            'ENHANCED_CPC' => 'Ulepszony CPC',
            'INVALID' => 'Nieprawidłowa',
            'MANUAL_CPA' => 'Ręczny CPA',
            'MANUAL_CPC' => 'Ręczny CPC',
            'MANUAL_CPM' => 'Ręczny CPM',
            'MANUAL_CPV' => 'Ręczny CPV',
            'MAXIMIZE_CONVERSIONS' => 'Maksymalizacja konwersji',
            'MAXIMIZE_CONVERSION_VALUE' => 'Maksymalizacja wartości konwersji',
            'PAGE_ONE_PROMOTED' => 'Promowanie pierwszej strony',
            'PERCENT_CPC' => 'Procentowy CPC',
            'TARGET_CPA' => 'Docelowy CPA',
            'TARGET_CPM' => 'Docelowy CPM',
            'TARGET_CPV' => 'Docelowy CPV',
            'TARGET_IMPRESSION_SHARE' => 'Docelowy udział w wyświetleniach',
            'TARGET_OUTRANK_SHARE' => 'Docelowa przewaga pozycji',
            'TARGET_ROAS' => 'Docelowy ROAS',
            'TARGET_SPEND' => 'Docelowe wydatki',
            default => $type ?: '-',
        };
    }
}
