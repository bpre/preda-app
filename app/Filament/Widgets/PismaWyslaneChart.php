<?php

namespace App\Filament\Widgets;

use App\Models\Letter;
use Filament\Widgets\ChartWidget;

class PismaWyslaneChart extends ChartWidget
{
    protected ?string $heading = 'Pisma wysłane';

    protected static ?int $sort = 220;

    protected function getData(): array
    {
        return bp_makeChart(Letter::class, 'out', 'type');
    }

    protected function getType(): string
    {
        return 'line';
    }
}
