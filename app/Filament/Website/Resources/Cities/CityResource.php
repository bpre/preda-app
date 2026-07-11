<?php

namespace App\Filament\Website\Resources\Cities;

use App\Filament\Website\Resources\Cities\Pages\CreateCity;
use App\Filament\Website\Resources\Cities\Pages\EditCity;
use App\Filament\Website\Resources\Cities\Pages\ListCities;
use App\Filament\Website\Resources\Cities\Schemas\CityForm;
use App\Filament\Website\Resources\Cities\Tables\CitiesTable;
use App\Models\Website\City;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $slug = 'miasta';

    protected static ?string $recordTitleAttribute = 'city';

    protected static ?string $navigationLabel = 'Miasta';
    protected static ?string $pluralModelLabel = 'Miasta';

    protected static ?string $modelLabel = 'Miasto';

    protected static string|UnitEnum|null $navigationGroup = 'Zasoby';
    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CitiesTable::configure($table);
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
            'index' => ListCities::route('/'),
            'create' => CreateCity::route('/create'),
            'edit' => EditCity::route('/{record}/edit'),
        ];
    }
}
