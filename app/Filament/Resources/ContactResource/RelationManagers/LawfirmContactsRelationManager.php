<?php

namespace App\Filament\Resources\ContactResource\RelationManagers;

use Filament\Schemas\Schema;
use App\Filament\Resources\ContactResource;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LawfirmContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'lawfirm_contacts';

    protected static ?string $title = 'Prawnicy kancelarii';


    public function form(Schema $schema): Schema
    {
        return ContactResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return ContactResource::table($table)->searchable(false);
    }
}
