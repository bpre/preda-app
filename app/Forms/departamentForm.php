<?php

namespace App\Forms;

use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Fieldset;
use App\Models\Contact;
use App\Models\Departament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\IconPosition;
use App\Filament\Resources\ContactResource\RelationManagers\DepartamentsRelationManager;


class departamentForm {

    public static function form($contact_id = null) {
        return [
            Hidden::make('contact_id')
                ->label('Kontakt')
                ->required()
                ->hiddenOn(DepartamentsRelationManager::class)
                ->default($contact_id)
                ->columnSpan(3),

            TextInput::make('label')
                ->label('Nazwa')
                ->required()
                ->maxLength(255)
                ->columnSpan(3),

            Actions::make([

                Action::make('wczytajDane')
                    ->link()
                    ->hidden(fn () => auth()->user()->role != 'admin')
                    ->schema(function (Departament $record) {

                        return [
                            Select::make('contact_id')
                                ->label('Kontakt, którego dane chcesz wczytać')
                                ->searchable()
                                ->options(Contact::orderBy('sort_name')->get()->pluck('sort_name', 'id'))
                        ];
                        }
                    )
                    ->action(function (Departament $record, $data, Set $set) {

                        $c = Contact::where('id', $data['contact_id'])->first();

                        $set('address', $c['address']);
                        $set('zip_code', $c['zip_code']);
                        $set('city', $c['city']);
                        $set('phone', $c['phone']);
                        $set('email', $c['email']);
                    }),

                Action::make('daneKontaktowe')
                    ->link()
                    ->icon(function(Get $get) {
                        return $get('details') ? 'heroicon-m-chevron-down' : 'heroicon-m-chevron-right';
                    })
                    ->iconPosition(IconPosition::After)
                    ->action(function (Set $set, Get $get) {
                        $set('details', !$get('details'));
                    }),

            ])->alignment(Alignment::End)->columnSpan(3),

            Fieldset::make('Dane kontaktowe')->schema([
                TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->label('E-mail'),
                TextInput::make('telefon')
                    ->tel()
                    ->maxLength(255),
            ])->hidden(fn (Get $get): string => $get('details') == false),

            Fieldset::make('Adres')->schema([
                TextInput::make('address')
                    ->label('Ulica i numer')
                    ->maxLength(255)
                    ->columnSpan(3),
                TextInput::make('zip_code')->label('Kod')
                    ->rules(['post_code'])
                    ->maxLength(255),
                TextInput::make('city')
                    ->label('Miejscowość')
                    ->maxLength(255)
                    ->columnSpan(2),
            ])->hidden(fn (Get $get): string => $get('details') == false)
            ->columns(3),
        ];
    }

}
