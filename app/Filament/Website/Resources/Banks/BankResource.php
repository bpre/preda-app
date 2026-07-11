<?php

namespace App\Filament\Website\Resources\Banks;

use App\Filament\Website\Resources\Banks\Pages\CreateBank;
use App\Filament\Website\Resources\Banks\Pages\EditBank;
use App\Filament\Website\Resources\Banks\Pages\ListBanks;
use App\Filament\Website\Resources\Banks\RelationManagers\CreditsRelationManager;
use App\Filament\Website\Resources\Banks\Schemas\BankForm;
use App\Filament\Website\Resources\Banks\Tables\BanksTable;
use App\Models\Website\Bank;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BankResource extends Resource
{
    protected static ?string $model = Bank::class;

    protected static ?string $slug = 'banki';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'Banki';
    protected static ?string $pluralModelLabel = 'Banki';

    protected static string|UnitEnum|null $navigationGroup = 'Zasoby';
    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BankForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BanksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CreditsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBanks::route('/'),
            'create' => CreateBank::route('/create'),
            'edit' => EditBank::route('/{record}/edit'),
        ];
    }
}
