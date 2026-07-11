<?php

namespace App\Filament\Website\Resources\Banks\RelationManagers;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use App\Enums\Website\Currencies;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\DissociateBulkAction;
use Filament\Resources\RelationManagers\RelationManager;

class CreditsRelationManager extends RelationManager
{
    protected static string $relationship = 'credits';
    protected static ?string $title = 'Umowy kredytowe';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('credit_name')
                    ->label('Nazwa')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(8),
                TextInput::make('credit_year')
                    ->label('Rok')
                    ->required()
                    ->maxLength(10)
                    ->columnSpan(2),
                Select::make('credit_currency')
                    ->options(Currencies::class)
                    ->label('Waluta')
                    ->native(false)
                    ->required()
                    ->columnSpan(5),
                Select::make('credit_type')
                    ->label('Typ')
                    ->options([
                        'indeksowany' => 'indeksowany',
                        'denominowany' => 'denominowany',
                    ])
                    ->native(false)
                    ->required()
                    ->columnSpan(5),
                Toggle::make('is_published')
                    ->label('Publikuj na stronie')
                    ->columnSpanFull(),

                Repeater::make('clauses')
                    ->label('Klauzule')
                    ->schema(
                        [

                                Textarea::make('clause')
                                    ->label('Treść')
                                    ->columnSpan(3)
                                    ->rows(3),
                                TextInput::make('item')
                                    ->label('Jednostka'),

                        ]
                    )->columnSpanFull()
            ])->columns(10);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('credit_name')
            ->columns([
                TextColumn::make('credit_name')
                    ->label('Nazwa')
                    ->searchable(),
                TextColumn::make('credit_year')
                    ->label('Rok')
                    ->searchable(),
                TextColumn::make('credit_type')
                    ->label('Top')
                    ->searchable(),
                TextColumn::make('credit_currency')
                    ->label('Waluta')
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label('Opublikowana?')
                    ->boolean(),
            ])
            ->reorderable('sort')
            ->defaultSort('sort')
            ->filters([
                SelectFilter::make('credit_currency')
                    ->options(Currencies::class)
                    ->native(false)
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
