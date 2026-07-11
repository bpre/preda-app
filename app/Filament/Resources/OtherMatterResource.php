<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Actions\EditAction;
use App\Filament\Resources\OtherMatterResource\Pages\ListOtherMatters;
use App\Filament\Resources\OtherMatterResource\Pages\CreateOtherMatter;
use App\Filament\Resources\OtherMatterResource\Pages\EditOtherMatter;
use Filament\Tables\Table;
use App\Models\OtherMatter;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\OtherMatterResource\Pages;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LettersRelationManager;
use App\Filament\Resources\MatterResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LawsuitsRelationManager;

class OtherMatterResource extends Resource
{
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'sprawy-inne';
    protected static ?string $model = OtherMatter::class;
    protected static ?string $recordTitleAttribute = 'label';
    protected static ?string $navigationLabel = 'Sprawy inne';
    protected static ?string $modelLabel = 'Sprawa inne';
    protected static ?string $pluralModelLabel = 'Sprawy inne';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = false;
    protected static bool $hasTitleCaseModelLabel = false;

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

    public static function getGloballySearchableAttributes(): array
    {
        return ['label', 'lawsuits.signature'];
    }
    public static function getGlobalSearchEloquentQuery(): EloquentBuilder
    {
        return parent::getGlobalSearchEloquentQuery()->where('is_archived', 0);
    }

    public static function form(Schema $schema): Schema
    {
        return MatterResource::form($schema, 'Sprawy inne');
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
            TextColumn::make('lawyer.name')
                ->label('Referat')
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('created_at')
                ->date()
                ->label('Data utworzenia')
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([

            // Referat
            SelectFilter::make('lawyer')->label('Referat')->relationship('lawyer', 'name')->native(false)

        ])
        ->recordActions([
            EditAction::make()->iconButton(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
            LettersRelationManager::class,
            LawsuitsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOtherMatters::route('/'),
            'create' => CreateOtherMatter::route('/create'),
            'edit' => EditOtherMatter::route('/{record}/edit'),
        ];
    }
}
