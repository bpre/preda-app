<?php

namespace App\Filament\Resources\ContactResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LetterResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class SenderLettersRelationManager extends RelationManager
{
    protected static string $relationship = 'sender_letters';

    protected static ?string $title = 'Korespondencja wysłana od tego podmiotu';

    public function form(Schema $schema): Schema
    {
        return LetterResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return LetterResource::table($table)->searchable(false)->emptyStateDescription('Nie wysłano jeszcze żadnej korespondencji od tego podmitu');
    }
}
