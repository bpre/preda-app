<?php

namespace App\Filament\Resources\ContactResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CHFMatterResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class OpponentLawfirmMattersRelationManager extends RelationManager
{
    protected static string $relationship = 'opponent_lawfirm_matters';

    protected static ?string $title = 'Sprawy, w których występuje kancelaria';

    public function form(Schema $schema): Schema
    {
        return CHFMatterResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return CHFMatterResource::table($table)->searchable(false);
    }
}
