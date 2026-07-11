<?php

namespace App\Filament\Resources\CHFMatterResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LetterResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class LettersRelationManager extends RelationManager
{
    protected static string $relationship = 'letters';

    protected static ?string $title = 'Korespondencja';

    protected static ?string $modelLabel = 'Korespondencja';
    protected static ?string $pluralModelLabel = 'Korespondencja';

    public function form(Schema $schema): Schema
    {
        return LetterResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return LetterResource::table($table)
            ->headerActions([
                CreateAction::make()->modalWidth('7xl')->createAnother(false)->modalHeading('Nowa korespondencja')->slideOver(),
            ]);
    }
}
