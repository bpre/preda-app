<?php

namespace App\Filament\Resources\LetterNotificationResource\Widgets;

use App\Services\LetterNotificationQueueMonitor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LetterNotificationQueueStatus extends BaseWidget
{
    protected ?string $pollingInterval = '5s';

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 1;
    }

    protected function getStats(): array
    {
        $monitor = app(LetterNotificationQueueMonitor::class);

        $monitor->dispatchHeartbeatIfNeeded();

        $status = $monitor->getStatusData();

        return [
            Stat::make('Kolejka natychmiastowej wysyłki', $status['label'])
                ->description($status['description'])
                ->descriptionIcon($status['icon'])
                ->color($status['color']),
        ];
    }
}
