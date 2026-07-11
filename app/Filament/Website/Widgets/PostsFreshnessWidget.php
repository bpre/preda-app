<?php

namespace App\Filament\Website\Widgets;

use App\Models\Website\Post;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PostsFreshnessWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Ostatnie publikacje';

    protected function getStats(): array
    {
        return [
            $this->statFor('blog', 'Blog'),
            $this->statFor('orzecznictwo', 'Orzecznictwo'),
        ];
    }

    protected function statFor(string $category, string $label): Stat
    {
        $last = Post::query()->where('category', $category)->max('created_at');

        if (!$last) {
            return Stat::make("$label", '—')
                ->description("Brak wpisów w „{$label}”")
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle');
        }

        $lastAt = Carbon::parse($last);
        $days   = $lastAt->diffInDays(now());

        [$status, $color, $icon] = match (true) {
            $days < 14 => ['OK', 'success', 'heroicon-o-check-circle'],
            $days < 28 => ['Czas dodać wpis', 'warning', 'heroicon-o-clock'],
            default    => ['Za długa przerwa', 'danger', 'heroicon-o-x-circle'],
        };

        return Stat::make("$label - $status", $lastAt->diffForHumans())
            ->description('Ostatni: ' . $lastAt->format('Y-m-d H:i'))
            ->color($color)
            ->icon($icon);
    }
}
