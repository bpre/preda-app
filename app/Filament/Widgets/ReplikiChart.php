<?php

namespace App\Filament\Widgets;

use App\Models\Stage;
use Filament\Widgets\ChartWidget;

class ReplikiChart extends ChartWidget
{
    protected ?string $heading = 'Repliki';
    protected static ?int $sort = 50;

    protected function getData(): array
    {
        return bp_makeChart(Stage::class, 26);
    }

    protected function getType(): string
    {
        return 'line';
    }
}
