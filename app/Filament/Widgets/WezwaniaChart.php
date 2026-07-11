<?php

namespace App\Filament\Widgets;

use App\Models\Stage;
use Filament\Widgets\ChartWidget;

class WezwaniaChart extends ChartWidget
{
    protected ?string $heading = 'Wezwania przedsądowe';
    protected static ?int $sort = 30;

    protected function getData(): array
    {
        return bp_makeChart(Stage::class, 21);
    }

    protected function getType(): string
    {
        return 'line';
    }
}
