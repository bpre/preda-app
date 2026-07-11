<?php

namespace App\Filament\Crm\Resources;

use App\Filament\Crm\Resources\LeadResource\Pages\CreateLead;
use App\Filament\Crm\Resources\LeadResource\Pages\EditLead;
use App\Filament\Crm\Resources\LeadResource\Pages\ListLeads;
use App\Filament\Crm\Resources\LeadResource\RelationManagers\StagesRelationManager;
use App\Filament\Resources\CHFMatterResource;
use App\Models\Lead;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'szanse';

    protected static ?string $model = Lead::class;

    protected static ?string $navigationLabel = 'Szanse';

    protected static ?string $modelLabel = 'Szanse';

    protected static ?string $pluralModelLabel = 'Szanse';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    protected function shouldPersistTableColumnSearchInSession(): bool
    {
        return true;
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function shouldPersistTableSearchInSession(): bool
    {
        return true;
    }

    protected function shouldPersistTableSortInSession(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return CHFMatterResource::form($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Medium)
                    ->searchable()
                    ->label('Sprawa'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->modalHeading('Nowa szansa'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            StagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'create' => CreateLead::route('/create'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }
}
