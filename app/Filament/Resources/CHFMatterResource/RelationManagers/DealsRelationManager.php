<?php

namespace App\Filament\Resources\CHFMatterResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DealResource;

class DealsRelationManager extends RelationManager
{
    protected static string $relationship = 'deals';

    protected static ?string $title = 'Zlecenia';

    protected static ?string $modelLabel = 'Zlecenie';
    protected static ?string $pluralModelLabel = 'Zlecenia';

    public function form(Schema $schema): Schema
    {
        return DealResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return DealResource::table($table);
    }
}
