<?php

namespace App\Filament\Resources\CHFMatterResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LawsuitResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class LawsuitsRelationManager extends RelationManager
{


    protected static string $relationship = 'lawsuits';

    protected static ?string $title = 'Postępowania';

    protected static ?string $modelLabel = 'Postępowanie';
    protected static ?string $pluralModelLabel = 'Postępowania';

    public function form(Schema $schema): Schema
    {
        return LawsuitResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return LawsuitResource::table($table)
        ->headerActions([
            CreateAction::make()->modalWidth('3xl')->createAnother(false)->modalHeading('Nowe postępowanie'),
        ]);
    }
}
