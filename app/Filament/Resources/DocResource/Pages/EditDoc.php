<?php

namespace App\Filament\Resources\DocResource\Pages;

use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Actions\DeleteAction;
use Filament\Actions;
use App\Models\Doctemplate;
use Filament\Forms\Components\Select;
use App\Filament\Resources\DocResource;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use App\Http\Controllers\PrintController;

class EditDoc extends EditRecord
{
    protected static string $resource = DocResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('Drukuj')
                ->color('gray')
                ->icon('heroicon-m-printer')
                ->action(fn ($record, $data) => PrintController::pismo($record)),

            Action::make('Wczytaj szablon')
                ->modalWidth('md')
                ->schema([
                    Select::make('template_id')
                        ->label('Szablon')
                        ->options(Doctemplate::get()->pluck('label', 'id'))
                        ->searchable()
                    ->required(),
                ])
                ->requiresConfirmation()
                ->modalDescription('Wczytanie szablonu spowoduje, że aktualna treść dokumentu zostanie zastąpiona.')
                ->action(function (Set $set) {
                    $set('body', 'abx...');
                }),

            DeleteAction::make(),
        ];
    }
}
