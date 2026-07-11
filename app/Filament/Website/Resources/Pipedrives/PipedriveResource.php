<?php

namespace App\Filament\Website\Resources\Pipedrives;

use App\Filament\Website\Resources\Pipedrives\Pages\CreatePipedrive;
use App\Filament\Website\Resources\Pipedrives\Pages\EditPipedrive;
use App\Filament\Website\Resources\Pipedrives\Pages\ListPipedrives;
use App\Filament\Website\Resources\Pipedrives\Schemas\PipedriveForm;
use App\Filament\Website\Resources\Pipedrives\Tables\PipedrivesTable;
use App\Models\Website\Pipedrive;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PipedriveResource extends Resource
{
    protected static ?string $model = Pipedrive::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PipedriveForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PipedrivesTable::configure($table);
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
            'index' => ListPipedrives::route('/'),
            'create' => CreatePipedrive::route('/create'),
            'edit' => EditPipedrive::route('/{record}/edit'),
        ];
    }
}
