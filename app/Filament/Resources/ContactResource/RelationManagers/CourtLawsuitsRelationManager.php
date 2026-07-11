<?php

namespace App\Filament\Resources\ContactResource\RelationManagers;

use Filament\Schemas\Schema;
use App\Filament\Resources\LawsuitResource;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourtLawsuitsRelationManager extends RelationManager
{
    protected static string $relationship = 'court_lawsuits';
    protected static ?string $title = 'Sąd';

    public function form(Schema $schema): Schema
    {
        return LawsuitResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return LawsuitResource::table($table)->searchable(false)->emptyStateDescription('Ten kontakt nie występuje jako sąd w żadnym postępowaniu.');
    }
}
