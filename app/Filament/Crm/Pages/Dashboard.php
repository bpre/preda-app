<?php

namespace App\Filament\Crm\Pages;

use App\Filament\Crm\Resources\CHFPotentialMatterResource;
use App\Filament\Crm\Widgets\LeadStatsWidget;
use App\Filament\Crm\Widgets\PotentialMattersRequiringActionWidget;
use App\Services\Crm\LeadStatsService;
use App\Support\Website\LeadTypes;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'CRM';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = 0;

    protected bool $persistsFiltersInSession = true;

    public static function shouldRegisterNavigation(): bool
    {
        return static::hasVisibleWidgets();
    }

    public static function getNavigationLabel(): string
    {
        return static::displaysOnlyLeadStats() ? 'Statystyki' : parent::getNavigationLabel();
    }

    public static function hasVisibleWidgets(): bool
    {
        return PotentialMattersRequiringActionWidget::canView()
            || LeadStatsWidget::canView();
    }

    public function mount(): void
    {
        if (! static::hasVisibleWidgets()) {
            $this->redirect(CHFPotentialMatterResource::getUrl(panel: 'crm'));
        }
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return static::displaysOnlyLeadStats() ? 'Statystyki' : parent::getTitle();
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return static::displaysOnlyLeadStats() ? $this->getTitle() : null;
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'md' => 2,
                'xl' => 4,
            ])
            ->components([
                Select::make('leadCurrency')
                    ->label('Waluta')
                    ->options([
                        'all' => 'Wszystkie',
                        'CHF' => 'Kredyt CHF',
                        'EUR' => 'Kredyt EUR',
                    ])
                    ->default('all')
                    ->native(false)
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateHydrated(function (Set $set, ?string $state): void {
                        if (blank($state)) {
                            $set('leadCurrency', 'all');
                        }
                    })
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        if (blank($state)) {
                            $set('leadCurrency', 'all');
                        }
                    })
                    ->visible(fn (): bool => LeadStatsWidget::canView()),
                Select::make('leadType')
                    ->label('Typ leada')
                    ->options(LeadTypes::filterOptions())
                    ->default('all')
                    ->native(false)
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateHydrated(function (Set $set, ?string $state): void {
                        if (blank($state)) {
                            $set('leadType', 'all');
                        }
                    })
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        if (blank($state)) {
                            $set('leadType', 'all');
                        }
                    })
                    ->visible(fn (): bool => LeadStatsWidget::canView()),
                Select::make('leadSource')
                    ->label('Źródło')
                    ->options(LeadStatsService::leadSourceFilterOptions())
                    ->default('all')
                    ->native(false)
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateHydrated(function (Set $set, ?string $state): void {
                        if (blank($state)) {
                            $set('leadSource', 'all');
                        }
                    })
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        if (blank($state)) {
                            $set('leadSource', 'all');
                        }
                    })
                    ->visible(fn (): bool => LeadStatsWidget::canView()),
                DateRangePicker::make('leadDateRange')
                    ->label('Okres statystyk leadów')
                    ->extraAttributes([
                        'class' => 'crm-dashboard-date-range-picker',
                    ])
                    ->format('Y-m-d')
                    ->useRangeLabels()
                    ->defaultLast30Days()
                    ->autoApply()
                    ->ranges([
                        'Dzisiaj' => [now()->startOfDay(), now()->endOfDay()],
                        'Ostatnie 7 dni' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
                        'Ostatnie 30 dni' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
                        'Ten miesiąc' => [now()->startOfMonth(), now()->endOfMonth()],
                        'Poprzedni miesiąc' => [
                            now()->subMonthNoOverflow()->startOfMonth(),
                            now()->subMonthNoOverflow()->endOfMonth(),
                        ],
                        'Ten rok' => [now()->startOfYear(), now()->endOfYear()],
                        'Poprzedni rok' => [
                            now()->subYearNoOverflow()->startOfYear(),
                            now()->subYearNoOverflow()->endOfYear(),
                        ],
                    ])
                    ->placeholder('Wybierz okres')
                    ->visible(fn (): bool => LeadStatsWidget::canView()),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        $visibleWidgetCategories = $this->visibleWidgetCategories();

        if (count($visibleWidgetCategories) === 1) {
            return $schema
                ->components($visibleWidgetCategories[0]['schema']);
        }

        if ($visibleWidgetCategories === []) {
            return $schema
                ->components([]);
        }

        return $schema
            ->components([
                Tabs::make()
                    ->key('crmDashboardTabs')
                    ->contained(false)
                    ->activeTab(1)
                    ->tabs(array_map(
                        fn (array $category): Tab => Tab::make($category['label'])
                            ->schema($category['schema']),
                        $visibleWidgetCategories,
                    )),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            PotentialMattersRequiringActionWidget::class,
            LeadStatsWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 1;
    }

    private function leadStatsExportUrl(): string
    {
        $filters = $this->filters ?? [];

        return route('crm.lead-stats.export', [
            'leadDateRange' => $filters['leadDateRange'] ?? null,
            'leadCurrency' => $filters['leadCurrency'] ?? 'all',
            'leadType' => $filters['leadType'] ?? 'all',
            'leadSource' => $filters['leadSource'] ?? 'all',
        ]);
    }

    private function visibleWidgetCategories(): array
    {
        $categories = [];

        if (PotentialMattersRequiringActionWidget::canView()) {
            $categories[] = [
                'label' => 'Sprawy wymagające działania',
                'schema' => $this->potentialMattersRequiringActionSchema(),
            ];
        }

        if (LeadStatsWidget::canView()) {
            $categories[] = [
                'label' => 'Statystyki leadów',
                'schema' => $this->leadStatsSchema(),
            ];
        }

        return $categories;
    }

    private static function displaysOnlyLeadStats(): bool
    {
        return LeadStatsWidget::canView()
            && ! PotentialMattersRequiringActionWidget::canView();
    }

    private function potentialMattersRequiringActionSchema(): array
    {
        return [
            Grid::make($this->getColumns())
                ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                    PotentialMattersRequiringActionWidget::class,
                ])),
        ];
    }

    private function leadStatsSchema(): array
    {
        return [
            $this->getFiltersFormContentComponent(),
            SchemaActions::make([
                Action::make('exportLeadStats')
                    ->label('Eksportuj')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (): string => $this->leadStatsExportUrl()),
            ])
                ->alignEnd()
                ->visible(fn (): bool => LeadStatsService::canExport()),
            Grid::make($this->getColumns())
                ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                    LeadStatsWidget::class,
                ])),
        ];
    }
}
