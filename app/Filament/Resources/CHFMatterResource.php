<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use App\Filament\Resources\CHFMatterResource\Pages\ListCHFMatters;
use App\Filament\Resources\CHFMatterResource\Pages\CreateCHFMatter;
use App\Filament\Resources\CHFMatterResource\Pages\EditCHFMatter;
use App\Models\CHFMatter;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\CHFMatterResource\Pages;
use Filament\Resources\RelationManagers\RelationGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use App\Filament\Resources\CHFMatterResource\RelationManagers\DealsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\OffersRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\StagesRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\CreditsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LettersRelationManager;
use App\Filament\Resources\MatterResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LawsuitsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\PaymentsRelationManager;

class CHFMatterResource extends Resource
{
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'chf';

    protected static ?string $model = CHFMatter::class;
    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'Sprawy CHF';
    protected static ?string $modelLabel = 'Sprawa CHF';
    protected static ?string $pluralModelLabel = 'Sprawy CHF';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationParentItem = 'Sprawy CHF';

    protected static bool $shouldRegisterNavigation = false;


    public static function getGloballySearchableAttributes(): array
    {
        return ['label', 'lawsuits.signature'];
    }
    public static function getGlobalSearchEloquentQuery(): EloquentBuilder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->where('is_archived', 0)
            ->where('is_matter', 1);
    }

    public static function form(Schema $schema): Schema
    {
        return MatterResource::form($schema);
    }

    public static function table(Table $table): Table
    {
        return MatterResource::table($table)
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderByRaw('COALESCE(current_stage_sort, -1)')
                ->orderBy('label')
                ->orderBy('id'));
    }

    public static function getRelations(): array
    {
        return [
            StagesRelationManager::class,
            ActivitiesRelationManager::class,
            CreditsRelationManager::class,
            LettersRelationManager::class,
            LawsuitsRelationManager::class,
            RelationGroup::make('Zlecenia', [
                OffersRelationManager::class,
                DealsRelationManager::class,
                PaymentsRelationManager::class
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCHFMatters::route('/'),
            'create' => CreateCHFMatter::route('/create'),
            'edit' => EditCHFMatter::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'view_matter'
        ];
    }
}
