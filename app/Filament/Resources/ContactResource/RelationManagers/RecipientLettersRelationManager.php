<?php

namespace App\Filament\Resources\ContactResource\RelationManagers;

use Filament\Schemas\Schema;
use App\Filament\Resources\LetterResource;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecipientLettersRelationManager extends RelationManager
{
    protected static string $relationship = 'recipient_letters';

    protected static ?string $title = 'Korespondencja otrzymana od tego podmiotu';

    public function form(Schema $schema): Schema
    {
        return LetterResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return LetterResource::table($table)->searchable(false)->emptyStateDescription('Nie otrzymano jeszcze żadnej korespondencji od tego podmitu');
    }
}
