<?php

namespace App\Filament\Resources\ContactResource\RelationManagers;

use Filament\Schemas\Schema;
use App\Filament\Resources\CreditResource;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactCreditsRelationManager extends RelationManager
{
    protected static string $relationship = 'contact_credits';

    protected static ?string $title = 'Kredytobiorca';

    public function form(Schema $schema): Schema
    {
        return CreditResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return CreditResource::table($table)->searchable(false)->emptyStateDescription('Ten kontakt nie występuje jako kredytobiorca w żadnej umowie.');
        ;
    }
}
