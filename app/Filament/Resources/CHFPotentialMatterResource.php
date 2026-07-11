<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use App\Filament\Resources\CHFPotentialMatterResource\Pages\ListCHFPotentialMatters;
use App\Filament\Resources\CHFPotentialMatterResource\Pages\CreateCHFPotentialMatter;
use App\Filament\Resources\CHFPotentialMatterResource\Pages\EditCHFPotentialMatter;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\CHFPotentialMatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Filament\Resources\CHFPotentialMatterResource\Pages;
use App\Filament\Resources\CHFPotentialMatterResource\RelationManagers;
use App\Filament\Resources\CHFMatterResource\RelationManagers\DealsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\OffersRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\StagesRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\CreditsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LettersRelationManager;
use App\Filament\Resources\MatterResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LawsuitsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\PaymentsRelationManager;

class CHFPotentialMatterResource extends Resource
{
    protected static ?string $model = CHFPotentialMatter::class;

    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'potencjalne';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'Potencjalne';
    protected static ?string $modelLabel = 'Potencjalne';
    protected static ?string $pluralModelLabel = 'Potencjalne sprawy';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;
    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $navigationParentItem = 'Sprawy CHF';

    public static function form(Schema $schema): Schema
    {
        return MatterResource::form($schema, 'CHF', $is_matter = false);
    }

    public static function table(Table $table): Table
    {
        return MatterResource::table($table, true)
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderByRaw('COALESCE(current_stage_sort, -1)')
                ->orderBy('label')
                ->orderBy('id'));
    }

    public static function getRelations(): array
    {
        return [
            StagesRelationManager::class,
            // ActivitiesRelationManager::class,
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
            'index' => ListCHFPotentialMatters::route('/'),
            'create' => CreateCHFPotentialMatter::route('/create'),
            'edit' => EditCHFPotentialMatter::route('/{record}/edit'),
        ];
    }
}
