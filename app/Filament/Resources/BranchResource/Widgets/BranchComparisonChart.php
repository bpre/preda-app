<?php

namespace App\Filament\Resources\BranchResource\Widgets;

use App\Models\Branch;
use Filament\Widgets\ChartWidget;

class BranchComparisonChart extends ChartWidget
{
    protected ?string $heading = 'Porównanie oddziałów';

    protected static ?int $sort = 10;

    protected function getData(): array
    {
        $branches = Branch::query()->ordered()->get();

        return [
            'datasets' => [
                [
                    'label' => 'Aktywne CHF',
                    'data' => $branches->map(fn (Branch $branch): int => $branch->activeChfMatters()->count())->all(),
                    'backgroundColor' => '#2563eb',
                ],
                [
                    'label' => 'Wszystkie CHF',
                    'data' => $branches->map(fn (Branch $branch): int => $branch->chfMatters()->count())->all(),
                    'backgroundColor' => '#94a3b8',
                ],
            ],
            'labels' => $branches->pluck('label')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
