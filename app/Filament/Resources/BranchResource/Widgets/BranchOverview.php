<?php

namespace App\Filament\Resources\BranchResource\Widgets;

use App\Models\Branch;
use App\Support\Branches\BranchReport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BranchOverview extends StatsOverviewWidget
{
    public ?Branch $record = null;

    protected ?string $heading = 'Podsumowanie oddziału';

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $report = BranchReport::make($this->record)->toArray();
        $totals = $report['totals'];
        $summary = $report['summary'];
        $balance = $totals['paid'] - $totals['expense'];

        return [
            Stat::make('Aktywne sprawy CHF', (string) $summary['active_matters'])
                ->description('Wszystkie: '.$summary['matters'])
                ->color('info'),
            Stat::make('Zakończone sprawy CHF', (string) $summary['ended_matters'])
                ->color('gray'),
            Stat::make('Przychody', bp_currency($totals['paid']))
                ->color('success'),
            Stat::make('Wydatki', bp_currency($totals['expense']))
                ->color('danger'),
            Stat::make('Bilans', bp_currency($balance))
                ->color($balance >= 0 ? 'success' : 'danger'),
            Stat::make('Przyszłe / potencjalne', bp_currency($totals['future']).' / '.bp_currency($totals['potential']))
                ->color('warning'),
        ];
    }
}
