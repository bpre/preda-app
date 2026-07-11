<?php

namespace App\Filament\Website\Resources\PageSnapshots;

use App\Filament\Website\Resources\PageSnapshots\Pages\CreatePageSnapshot;
use App\Filament\Website\Resources\PageSnapshots\Pages\EditPageSnapshot;
use App\Filament\Website\Resources\PageSnapshots\Pages\ListPageSnapshots;
use App\Filament\Website\Resources\PageSnapshots\Schemas\PageSnapshotForm;
use App\Filament\Website\Resources\PageSnapshots\Tables\PageSnapshotsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Models\Website\PageSnapshot;

class PageSnapshotResource extends Resource
{
    protected static ?string $model = PageSnapshot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PageSnapshotForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PageSnapshotsTable::configure($table);
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
            'index' => ListPageSnapshots::route('/'),
            'create' => CreatePageSnapshot::route('/create'),
            'edit' => EditPageSnapshot::route('/{record}/edit'),
        ];
    }
}
