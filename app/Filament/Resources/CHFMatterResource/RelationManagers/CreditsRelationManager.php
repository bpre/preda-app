<?php

namespace App\Filament\Resources\CHFMatterResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CreditResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class CreditsRelationManager extends RelationManager
{
    protected static string $relationship = 'credits';

    protected static ?string $title = 'Umowy kredytowe';

    protected static ?string $modelLabel = 'Umowa kredytowa';
    protected static ?string $pluralModelLabel = 'Umowy kredytowe';

    public function form(Schema $schema): Schema
    {
        return CreditResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return CreditResource::table($table)
            ->headerActions([
                CreateAction::make()->slideOver()->modalWidth('7xl')->createAnother(false)->modalHeading('Nowa umowa kredytowa'),
            ]);
    }
}
