<?php

namespace App\Filament\Crm\Resources;

use App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages\CreateCHFPotentialMatter;
use App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages\EditCHFPotentialMatter;
use App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages\ListCHFPotentialMatters;
use App\Filament\Resources\CHFMatterResource\RelationManagers\CreditsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\DealsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\StagesRelationManager;
use App\Filament\Resources\MatterResource;
use App\Filament\Resources\MatterResource\RelationManagers\ActivitiesRelationManager;
use App\Models\CHFPotentialMatter;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CHFPotentialMatterResource extends Resource
{
    protected static ?string $model = CHFPotentialMatter::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'potencjalne';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'Potencjalne sprawy';

    protected static ?string $modelLabel = 'Potencjalna sprawa';

    protected static ?string $pluralModelLabel = 'Potencjalne sprawy';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

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
            DealsRelationManager::class,
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
