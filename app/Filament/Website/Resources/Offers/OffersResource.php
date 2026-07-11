<?php

namespace App\Filament\Website\Resources\Offers;

use App\Filament\Website\Resources\Offers\Pages\CreateOffers;
use App\Filament\Website\Resources\Offers\Pages\EditOffers;
use App\Filament\Website\Resources\Offers\Pages\ListOffers;
use App\Filament\Website\Resources\Offers\Schemas\OffersForm;
use App\Filament\Website\Resources\Offers\Tables\OffersTable;
use App\Models\Website\Offer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OffersResource extends Resource
{
    protected static ?string $model = Offer::class;

    protected static ?string $slug = 'zapytania-ofertowe';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Zapytania ofertowe';
    protected static ?string $pluralModelLabel = 'Zapytania ofertowe';
        protected static ?string $modelLabel = 'Zapytanie ofertowe';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Schema $schema): Schema
    {
        return OffersForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OffersTable::configure($table);
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
            'index' => ListOffers::route('/'),
            'create' => CreateOffers::route('/create'),
            'edit' => EditOffers::route('/{record}/edit'),
        ];
    }
}
