<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ExchangeRateResource\Pages\ListExchangeRates;
use App\Filament\Resources\ExchangeRateResource\Pages\EditExchangeRate;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ExchangeRate;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ExchangeRateResource\Pages;
use App\Filament\Resources\ExchangeRateResource\RelationManagers;

class ExchangeRateResource extends Resource
{
    protected static ?string $model = ExchangeRate::class;

    protected static ?int $navigationSort = 11;
    protected static ?string $slug = 'kursy';
    protected static ?string $navigationLabel = 'Kursy walut';
    protected static ?string $modelLabel = 'Kurs';
    protected static ?string $pluralModelLabel = 'Kursy walut';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Administracja';
    protected static bool $hasTitleCaseModelLabel = false;


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                DatePicker::make('date')->label('Data')->required(),
                TextInput::make('chf')->label('CHF')->required(),
                TextInput::make('eur')->label('EUR')->required(),
                TextInput::make('usd')->label('USD')->required()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('date')->label('Data')->sortable()->searchable(),
                TextColumn::make('chf')->label('CHF'),
                TextColumn::make('eur')->label('EUR'),
                TextColumn::make('usd')->label('USD'),

            ])
            ->defaultSort('date', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExchangeRates::route('/'),
            // 'create' => Pages\CreateExchangeRate::route('/create'),
            'edit' => EditExchangeRate::route('/{record}/edit'),
        ];
    }
}
