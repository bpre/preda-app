<?php

namespace App\Filament\Widgets;

use App\Models\Stage;
use Filament\Widgets\ChartWidget;

class PozwyChart extends ChartWidget
{
    protected ?string $heading = 'Złożone pozwy';
    protected static ?int $sort = 40;
    protected function getData(): array
    {
        return bp_makeChart(Stage::class, 23);
    }

    protected function getType(): string
    {
        return 'line';
    }
}
