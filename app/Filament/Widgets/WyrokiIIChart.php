<?php

namespace App\Filament\Widgets;

use App\Models\Stage;
use Filament\Widgets\ChartWidget;

class WyrokiIIChart extends ChartWidget
{
    protected ?string $heading = 'Wyroki II instancji';

    protected static ?int $sort = 80;

    protected function getData(): array
    {
        return bp_makeChart(Stage::class, 35);
    }

    protected function getType(): string
    {
        return 'line';
    }
}
