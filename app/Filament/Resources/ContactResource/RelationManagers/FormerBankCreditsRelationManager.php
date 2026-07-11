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

class FormerBankCreditsRelationManager extends RelationManager
{
    protected static string $relationship = 'former_bank_credits';

    protected static ?string $title = 'Bank (na umowie)';

    public function form(Schema $schema): Schema
    {
        return CreditResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return CreditResource::table($table)->searchable(false)->emptyStateDescription('Ten kontakt nie występuje jako „Bank (na umowie)” w żadnej umowie kredytowej.');
    }
}
