<?php

namespace App\Filament\Widgets;

use App\Models\Stage;
use Filament\Widgets\ChartWidget;

class WyrokiIChart extends ChartWidget
{
    protected ?string $heading = 'Wyroki I instancji';

    protected static ?int $sort = 60;

    protected function getData(): array
    {
        return bp_makeChart(Stage::class, 29);
    }

    protected function getType(): string
    {
        return 'line';
    }
}
