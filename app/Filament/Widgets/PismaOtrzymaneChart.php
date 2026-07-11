<?php

namespace App\Filament\Widgets;

use App\Models\Letter;
use Filament\Widgets\ChartWidget;

class PismaOtrzymaneChart extends ChartWidget
{
    protected ?string $heading = 'Pisma otrzymane';

    protected static ?int $sort = 210;

    protected function getData(): array
    {
        return bp_makeChart(Letter::class, 'in', 'type');
    }

    protected function getType(): string
    {
        return 'line';
    }
}
