<?php

namespace App\Filament\Resources\NeostampResource\Pages;

use Filament\Actions\Action;
use Filament\Schemas\Components\Group;
use Filament\Actions;
use App\BP\neoznaczki;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\NeostampResource;

class ListNeostamps extends ListRecords
{
    protected static string $resource = NeostampResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('BP OCR')->label('BP OCR')
                ->hidden()
                ->action(function() {
                    $neo = new neoznaczki();
                    $neo->bp_ocr('../storage/app/neoznaczki/2024-04-14/_numer2.png', true);
                }),

            Action::make('add_neostamps')
                ->label('Dodaj Neoznaczki')
                ->schema([

                    Group::make()->schema([
                        FileUpload::make('koperta_pdf')
                        ->label('Plik PDF z kopertami')
                        ->hint('Ze strony Envelo należy pobrać koperty w formacie C5.')
                        ->disk('local')
                        ->directory('neoznaczki')
                        ->columnSpan(2),

                    Select::make('type')
                        ->label('Typ')
                        ->options(NeostampResource::types())
                        ->native(false)
                        ->required(),

                    DatePicker::make('expiration_date')
                        ->label('Data ważności')
                        ->required()

                    ])->columns(2)

                ])
                ->action(function ($data) {

                    $neo = new neoznaczki();
                    $create = $neo->create('../storage/app/' . $data['koperta_pdf'], $data['type'], $data['expiration_date']);

                    if($create['dodane']) {
                        Notification::make()->title('Dodano Neoznaczki: ' .$create['dodane'])->success()->send();
                    }

                    if($create['pominiete']) {
                        Notification::make()->title('Pominięto duplikaty: ' .$create['pominiete'])->danger()->send();
                    }

                }),

            // Actions\CreateAction::make(),
        ];
    }
}
