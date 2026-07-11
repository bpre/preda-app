<?php

namespace App\Filament\Resources\ContactResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CreditResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class CurrentBankCreditsRelationManager extends RelationManager
{
    protected static string $relationship = 'current_bank_credits';

    protected static ?string $title = 'Bank (obecnie)';

    public function form(Schema $schema): Schema
    {
        return CreditResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return CreditResource::table($table)->searchable(false)->emptyStateDescription('Ten kontakt nie występuje jako „Bank (obecnie)” w żadnej umowie kredytowej.');
    }
}
