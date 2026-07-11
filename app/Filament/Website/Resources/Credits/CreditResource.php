<?php

namespace App\Filament\Website\Resources\Credits;

use App\Filament\Website\Resources\Credits\Pages\CreateCredit;
use App\Filament\Website\Resources\Credits\Pages\EditCredit;
use App\Filament\Website\Resources\Credits\Pages\ListCredits;
use App\Filament\Website\Resources\Credits\Schemas\CreditForm;
use App\Filament\Website\Resources\Credits\Tables\CreditsTable;
use App\Models\Website\Credit;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CreditResource extends Resource
{
    protected static ?string $model = Credit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'credit_name';

    protected static ?string $navigationLabel = 'Umowy kredytowe';
    protected static ?string $pluralModelLabel = 'Umowy kredytowe';
    protected static string|UnitEnum|null $navigationGroup = 'Zasoby';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CreditForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CreditsTable::configure($table);
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
            'index' => ListCredits::route('/'),
            'create' => CreateCredit::route('/create'),
            'edit' => EditCredit::route('/{record}/edit'),
        ];
    }
}
