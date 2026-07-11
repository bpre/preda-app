<?php

namespace App\Filament\Website\Resources\Offices;

use App\Filament\Website\Resources\Offices\Pages\CreateOffice;
use App\Filament\Website\Resources\Offices\Pages\EditOffice;
use App\Filament\Website\Resources\Offices\Pages\ListOffices;
use App\Filament\Website\Resources\Offices\Schemas\OfficeForm;
use App\Filament\Website\Resources\Offices\Tables\OfficesTable;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Models\Website\Office;

class OfficeResource extends Resource
{
    protected static ?string $model = Office::class;

    protected static ?string $slug = 'oddzialy';

    protected static ?string $recordTitleAttribute = 'city';

    protected static ?string $navigationLabel = 'Oddziały kancelarii';
    protected static ?string $pluralModelLabel = 'Oddziały kancelarii';

    protected static ?string $modelLabel = 'Oddział kancelarii';

    protected static string|UnitEnum|null $navigationGroup = 'Zasoby';
    protected static ?int $navigationSort = 7;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $hasTitleCaseModelLabel = false;


    public static function form(Schema $schema): Schema
    {
        return OfficeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficesTable::configure($table);
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
            'index' => ListOffices::route('/'),
            'create' => CreateOffice::route('/create'),
            'edit' => EditOffice::route('/{record}/edit'),
        ];
    }
}
