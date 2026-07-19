<?php

namespace App\Filament\Crm\Resources;

use App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages\CreateCHFPotentialMatter;
use App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages\EditCHFPotentialMatter;
use App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages\ListCHFPotentialMatters;
use App\Filament\Crm\Resources\CHFPotentialMatterResource\RelationManagers\ClientMessagesRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\CreditsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\DealsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\StagesRelationManager;
use App\Filament\Resources\MatterResource;
use App\Filament\Resources\MatterResource\RelationManagers\ActivitiesRelationManager;
use App\Models\CHFPotentialMatter;
use App\Support\Crm\ClientAcquisitionAccess;
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
        $canUseClientAcquisition = ClientAcquisitionAccess::canUse();

        $table = MatterResource::table(
            $table,
            show_created_at: true,
            stages_hidden: false,
            show_next_action: $canUseClientAcquisition,
            hidden_table_columns: [
                'currentStage.parent',
                'is_matter',
                'is_archived',
                'start',
                'end',
            ],
            show_my_referat_filter: true,
            my_referat_filter_default: $canUseClientAcquisition,
        );

        if (! $canUseClientAcquisition) {
            return $table->defaultSort(fn (Builder $query): Builder => $query
                ->orderByDesc('created_at')
                ->orderByDesc('id'));
        }

        return $table->defaultSort(function (Builder $query): Builder {
            $today = now()->toDateString();

            return $query
                ->orderByRaw(
                    <<<'SQL'
                        CASE
                            WHEN next_action_due_at IS NOT NULL AND DATE(next_action_due_at) < ? THEN 0
                            WHEN next_action_due_at IS NOT NULL AND DATE(next_action_due_at) = ? THEN 1
                            WHEN next_action_due_at IS NOT NULL AND DATE(next_action_due_at) > ? THEN 2
                            ELSE 3
                        END
                        SQL,
                    [$today, $today, $today],
                )
                ->orderBy('next_action_due_at')
                ->orderByRaw('COALESCE(current_stage_sort, 999999)')
                ->orderByDesc('created_at')
                ->orderBy('label')
                ->orderBy('id');
        });
    }

    public static function getRelations(): array
    {
        return [
            StagesRelationManager::class,
            ...(ClientAcquisitionAccess::canUse() ? [
                ClientMessagesRelationManager::class,
            ] : []),
            ActivitiesRelationManager::class,
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
