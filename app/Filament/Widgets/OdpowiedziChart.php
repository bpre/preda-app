<?php

namespace App\Filament\Widgets;

use App\Models\Stage;
use Filament\Widgets\ChartWidget;

class OdpowiedziChart extends ChartWidget
{
    protected ?string $heading = 'Odpowiedzi na apelacje';
    protected static ?int $sort = 70;
    protected function getData(): array
    {
        return bp_makeChart(Stage::class, 33);
    }

    protected function getType(): string
    {
        return 'line';
    }
}
