<?php

namespace App\Filament\Resources\ContactResource\RelationManagers;

use Filament\Schemas\Schema;
use App\Filament\Resources\DepartamentResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class DepartamentsRelationManager extends RelationManager
{
    protected static string $relationship = 'departaments';
    protected static ?string $title = 'Jednostki organizacyjne';

    protected static ?string $modelLabel = 'Jednostka organizacyjna';
    protected static ?string $pluralModelLabel = 'Jednostki organizacyjne';
    public function form(Schema $schema): Schema
    {
        return DepartamentResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return DepartamentResource::table($table)->searchable(false);
    }
}
