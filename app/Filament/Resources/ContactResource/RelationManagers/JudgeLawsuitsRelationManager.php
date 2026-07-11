<?php

namespace App\Filament\Resources\ContactResource\RelationManagers;

use Filament\Schemas\Schema;
use App\Filament\Resources\LawsuitResource;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class JudgeLawsuitsRelationManager extends RelationManager
{
    protected static string $relationship = 'judge_lawsuits';

    protected static ?string $title = 'Sędzia';


    public function form(Schema $schema): Schema
    {
        return LawsuitResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return LawsuitResource::table($table)->searchable(false)->emptyStateDescription('Ten kontakt nie występuje jako sędzia w żadnym postępowaniu.');
    }
}
