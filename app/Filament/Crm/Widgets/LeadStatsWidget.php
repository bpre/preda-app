<?php

namespace App\Filament\Crm\Widgets;

use App\Services\Crm\LeadStatsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadStatsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;

    protected ?string $heading = 'Statystyki leadów';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 20;

    protected int|array|null $columns = [
        'md' => 2,
        'xl' => 5,
    ];

    public static function canView(): bool
    {
        return LeadStatsService::canView();
    }

    protected function getStats(): array
    {
        $stats = app(LeadStatsService::class)->stats($this->pageFilters);
        $total = $stats['total'];
        $previous = $stats['previous'];

        return [
            $this->withComparison(
                Stat::make('Leady razem', (string) $total)
                    ->description($this->comparisonDescription($total, $previous['total']))
                    ->icon('heroicon-o-inbox-stack')
                    ->color('info'),
                $total,
                $previous['total'],
            ),
            $this->withComparison(
                Stat::make('Przed kwalifikacją', (string) $stats['new'])
                    ->description($this->comparisonDescription($stats['new'], $previous['new']))
                    ->icon('heroicon-o-sparkles')
                    ->color('gray'),
                $stats['new'],
                $previous['new'],
            ),
            $this->withComparison(
                Stat::make('Zakwalifikowane', (string) $stats['qualified'])
                    ->description($this->comparisonDescription($stats['qualified'], $previous['qualified']))
                    ->icon('heroicon-o-check-circle')
                    ->color('success'),
                $stats['qualified'],
                $previous['qualified'],
            ),
            $this->withComparison(
                Stat::make('Odrzucone', (string) $stats['rejected'])
                    ->description($this->comparisonDescription($stats['rejected'], $previous['rejected']))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger'),
                $stats['rejected'],
                $previous['rejected'],
                positiveIsGood: false,
            ),
            $this->withComparison(
                Stat::make('Zlecone sprawy', (string) $stats['retained'])
                    ->description($this->comparisonDescription($stats['retained'], $previous['retained']))
                    ->icon('heroicon-o-briefcase')
                    ->color('success'),
                $stats['retained'],
                $previous['retained'],
            ),
        ];
    }

    private function withComparison(Stat $stat, int $current, int $previous, bool $positiveIsGood = true): Stat
    {
        return $stat
            ->descriptionIcon($this->comparisonIcon($current, $previous), 'before')
            ->descriptionColor($this->comparisonColor($current, $previous, $positiveIsGood));
    }

    private function comparisonDescription(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current === 0
                ? 'bez zmian'
                : '+100%';
        }

        $change = (($current - $previous) / $previous) * 100;

        if (abs($change) < 0.05) {
            return 'bez zmian';
        }

        return $this->formatSignedPercentage($change);
    }

    private function comparisonIcon(int $current, int $previous): string
    {
        return match ($this->comparisonDirection($current, $previous)) {
            'up' => 'heroicon-m-arrow-trending-up',
            'down' => 'heroicon-m-arrow-trending-down',
            default => 'heroicon-m-minus',
        };
    }

    private function comparisonColor(int $current, int $previous, bool $positiveIsGood): string
    {
        return match ($this->comparisonDirection($current, $previous)) {
            'up' => $positiveIsGood ? 'success' : 'danger',
            'down' => $positiveIsGood ? 'danger' : 'success',
            default => 'gray',
        };
    }

    private function comparisonDirection(int $current, int $previous): string
    {
        if ($current === $previous) {
            return 'flat';
        }

        return $current > $previous ? 'up' : 'down';
    }

    private function formatSignedPercentage(float $percentage): string
    {
        $formatted = number_format(abs($percentage), 1, ',', ' ');

        if (str_ends_with($formatted, ',0')) {
            $formatted = substr($formatted, 0, -2);
        }

        return ($percentage > 0 ? '+' : '-').$formatted.'%';
    }
}
