<?php

namespace App\Filament\Website\Resources\Securities;

use App\Filament\Website\Resources\Securities\Pages\CreateSecurity;
use App\Filament\Website\Resources\Securities\Pages\EditSecurity;
use App\Filament\Website\Resources\Securities\Pages\ListSecurities;
use App\Filament\Website\Resources\Securities\Schemas\SecurityForm;
use App\Filament\Website\Resources\Securities\Tables\SecuritiesTable;
use App\Models\Website\Security;
use BackedEnum, UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SecurityResource extends Resource
{
    protected static ?string $model = Security::class;

    protected static ?string $slug = 'zabezpieczenia';

    protected static ?string $navigationLabel = 'Zabezpieczenia';
    protected static ?string $pluralModelLabel = 'Zabezpieczenia';
    protected static ?string $modelLabel = 'Zabezpieczenie';

    protected static string|UnitEnum|null $navigationGroup = 'Zasoby';
    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'sign';

    public static function form(Schema $schema): Schema
    {
        return SecurityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SecuritiesTable::configure($table);
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
            'index' => ListSecurities::route('/'),
            'create' => CreateSecurity::route('/create'),
            'edit' => EditSecurity::route('/{record}/edit'),
        ];
    }
}
