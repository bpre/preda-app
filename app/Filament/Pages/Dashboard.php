<?php

namespace App\Filament\Pages;

use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;

class Dashboard extends \Filament\Pages\Dashboard
{

    use HasFiltersForm;

    protected static ?string $title = 'Kancelaria';

    // public function filtersForm(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Section::make()
    //                 ->schema([
    //                     DatePicker::make('startDate')
    //                         ->label('Od:')
    //                         ->inlineLabel()
    //                         ->hidden(fn (Get $get): string => $get('okres') != 'custom'),
    //                     DatePicker::make('endDate')
    //                         ->label('Do:')
    //                         ->inlineLabel()
    //                         ->hidden(fn (Get $get): string => $get('okres') != 'custom'),
    //                     Select::make('okres')
    //                         ->hiddenLabel()
    //                         ->options([
    //                             'this_month' => 'ten miesiąc',
    //                             'prev_month' => 'poprzedni miesiąc',
    //                             'this_year' => 'ten rok',
    //                             'prev_year' => 'poprzedni rok',
    //                             'all' => 'wszystkie'
    //                         ])
    //                         ->live()
    //                         ->default('this_month')
    //                         ->required()
    //                         ->afterStateUpdated(function (Set $set, ?string $state) {

    //                             if($state == null) {
    //                                 $set('okres', 'all');
    //                             }

    //                             switch($state) {
    //                                 case 'this_month':
    //                                     $set('startDate', date("Y-m-d", strtotime("first day of this month")));
    //                                     $set('endDate', date("Y-m-d", strtotime("last day of this month")));
    //                                     break;
    //                                 case 'prev_month':
    //                                     $set('startDate', date("Y-m-d", strtotime("first day of previous month")));
    //                                     $set('endDate', date("Y-m-d", strtotime("last day of previous month")));
    //                                     break;
    //                                 case 'this_year':
    //                                     $set('startDate', date("Y-m-d", strtotime("first day of january this year")));
    //                                     $set('endDate', date("Y-m-d", strtotime("last day of december this year")));
    //                                     break;
    //                                 case 'prev_year':
    //                                     $set('startDate', date("Y-m-d", strtotime("first day of january previous year")));
    //                                     $set('endDate', date("Y-m-d", strtotime("last day of december previous year")));
    //                                     break;
    //                                 case 'all':
    //                                 default:
    //                                     $set('startDate', null);
    //                                     $set('endDate', null);
    //                                     break;

    //                             }
    //                         })
    //                         ->columnStart(3)
    //                         ->native(false)
    //                 ])
    //                 ->columns(3),
    //         ]);
    // }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         FilterAction::make()
    //             ->form([
    //                 DatePicker::make('startDate')->label('Data początkowa'),
    //                 DatePicker::make('endDate')->label('Data końcowa'),
    //                 // ...
    //             ]),
    //     ];
    // }
}
