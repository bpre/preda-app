<?php

namespace App\Filament\Website\Resources\Users;

use App\Filament\Website\Resources\Users\Pages\EditUser;
use App\Filament\Website\Resources\Users\Pages\ListUsers;
use App\Filament\Website\Resources\Users\Schemas\UserForm;
use App\Filament\Website\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'pracownicy';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Pracownicy';

    protected static ?string $pluralModelLabel = 'Pracownicy';

    protected static string|UnitEnum|null $navigationGroup = 'Zasoby';

    protected static ?int $navigationSort = 9;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
