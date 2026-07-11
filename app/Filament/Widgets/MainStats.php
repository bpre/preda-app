<?php
/*
namespace App\Filament\Widgets;

use App\Models\Stage;
use App\Models\Letter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class MainStats extends BaseWidget
{

    use InteractsWithPageFilters;
    protected function getStats(): array
    {

        function makeStat($stage_id, $filters, $class = Stage::class, $field = 'stage_id') {

            // dd($stage_id);

            $startDate = $filters['startDate'] ?? null;
            $endDate = $filters['endDate'] ?? null;

            if($filters['okres'] != 'all') {

                $okres = explode('_', $filters['okres']);
                $period = $okres[1];

                if($startDate && $endDate) {
                    $prevStartDate = date("Y-m-d", strtotime("-1 $period", strtotime($startDate)));
                    $prevEndDate = date("Y-m-d", strtotime("last day of previous $period", strtotime($endDate)));;
                }

                $analizy['value'] = $class::query()
                    ->when($startDate, fn (Builder $query) => $query->whereDate('date', '>=', $startDate))
                    ->when($endDate, fn (Builder $query) => $query->whereDate('date', '<=', $endDate))
                    ->where($field, $stage_id)
                    ->count();

                $analizy['prev_value'] = $class::query()
                    ->when($prevStartDate, fn (Builder $query) => $query->whereDate('date', '>=', $prevStartDate))
                    ->when($prevEndDate, fn (Builder $query) => $query->whereDate('date', '<=', $prevEndDate))
                    ->where($field, $stage_id)
                    ->count();

            } else {
                $startDate = null;
                $endDate = null;
                $prevStartDate = null;
                $prevEndDate = null;

                $analizy['value'] = $class::query()
                    ->whereNotNull('date')
                    ->where($field, $stage_id)->count();

                $analizy['prev_value'] = $class::query()
                    ->whereNotNull('date')
                    ->where($field, $stage_id)->count();

            }



            if($analizy['prev_value'] == $analizy['value']) {

                $analizy['description'] = 'Bez zmian';
                $analizy['description_icon'] = 'heroicon-m-minus';
                $analizy['color'] = 'warning';
                $analizy['chart'] = [0, 0];

            } elseif($analizy['prev_value'] == 0) {

                $analizy['description'] = 'Wzrost o 100%';
                $analizy['description_icon'] = 'heroicon-m-arrow-trending-up';
                $analizy['color'] = 'success';
                $analizy['chart'] = [0, 100];

            } else {

                $aktualny_procent = round($analizy['value'] * 100 / $analizy['prev_value']);

                $change_type = $aktualny_procent > 100 ? 'wzrost' : 'spadek';

                $change_value = $aktualny_procent > 100 ? $aktualny_procent-100 : 100-$aktualny_procent;

                $analizy['description'] = $change_type .' o '.$change_value.' %';

                $analizy['description_icon'] = $change_type == 'wzrost' ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

                $analizy['chart'] = [$analizy['prev_value'], $analizy['value']];

                $analizy['color'] = $change_type == 'wzrost' ? 'success' : 'danger';

            }

            return $analizy;

        }

        $analizy = makeStat('5', $this->filters);
        $wezwania = makeStat('21', $this->filters);
        $pozwy = makeStat('23', $this->filters);
        $repliki = makeStat('26', $this->filters);
        $wyroki_i = makeStat('29', $this->filters);
        $odpowiedzi = makeStat('29', $this->filters);
        $wyroki_ii = makeStat('35', $this->filters);
        $letter_in = makeStat('in', $this->filters, Letter::class, 'type');
        $letter_out = makeStat('out', $this->filters, Letter::class, 'type');

        return [

            Stat::make('Analizy umów', $analizy['value'])
                ->description($analizy['description'])
                ->descriptionIcon($analizy['description_icon'])
                ->chart($analizy['chart'])
                ->color($analizy['color']),

            Stat::make('Wezwania przedsądowe', $wezwania['value'])
                ->description($wezwania['description'])
                ->descriptionIcon($wezwania['description_icon'])
                ->chart($wezwania['chart'])
                ->color($wezwania['color']),

            Stat::make('Pozwy', $pozwy['value'])
                ->description($pozwy['description'])
                ->descriptionIcon($pozwy['description_icon'])
                ->chart($pozwy['chart'])
                ->color($pozwy['color']),

            Stat::make('Repliki', $repliki['value'])
                ->description($repliki['description'])
                ->descriptionIcon($repliki['description_icon'])
                ->chart($repliki['chart'])
                ->color($repliki['color']),

            Stat::make('Wyroki I instancji', $wyroki_i['value'])
                ->description($wyroki_i['description'])
                ->descriptionIcon($wyroki_i['description_icon'])
                ->chart($wyroki_i['chart'])
                ->color($wyroki_i['color']),

            Stat::make('Odpowiedzi na apelacje', $odpowiedzi['value'])
                ->description($odpowiedzi['description'])
                ->descriptionIcon($odpowiedzi['description_icon'])
                ->chart($odpowiedzi['chart'])
                ->color($odpowiedzi['color']),

            Stat::make('Wyroki II instancji', $wyroki_ii['value'])
                ->description($wyroki_ii['description'])
                ->descriptionIcon($wyroki_ii['description_icon'])
                ->chart($wyroki_ii['chart'])
                ->color($wyroki_ii['color']),

            Stat::make('Pisma przychodzące', $letter_in['value'])
                ->description($letter_in['description'])
                ->descriptionIcon($wyroki_ii['description_icon'])
                ->chart($letter_in['chart'])
                ->color($letter_in['color']),

            Stat::make('Pisma wychodzące', $letter_out['value'])
                ->description($letter_out['description'])
                ->descriptionIcon($letter_out['description_icon'])
                ->chart($letter_out['chart'])
                ->color($letter_out['color']),
        ];

    }
}
*/
