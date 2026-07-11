<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use App\Filament\Resources\CHFPaymentMatterResource\Pages\ListCHFPaymentMatters;
use App\Filament\Resources\CHFPaymentMatterResource\Pages\CreateCHFPaymentMatter;
use App\Filament\Resources\CHFPaymentMatterResource\Pages\EditCHFPaymentMatter;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\CHFPaymentMatter;
use Filament\Resources\Resource;
use App\Filament\Resources\MatterResource;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Filament\Resources\CHFPaymentMatterResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use App\Filament\Resources\CHFMatterResource\RelationManagers\DealsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\StagesRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\CreditsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LettersRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LawsuitsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\PaymentsRelationManager;

class CHFPaymentMatterResource extends Resource
{

    public static function getGlobalSearchEloquentQuery(): EloquentBuilder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->where('is_archived', 0);
    }
    protected static ?string $model = CHFPaymentMatter::class;

    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'o-zaplate';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'O zapłatę';
    protected static ?string $modelLabel = 'O zapłatę';
    protected static ?string $pluralModelLabel = 'Sprawy o zapłatę';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    // protected static bool $shouldRegisterNavigation = false;
    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $navigationParentItem = 'Sprawy CHF';

    public static function form(Schema $schema): Schema
    {
        return MatterResource::form($schema, 'O zapłatę');
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
            // ActivitiesRelationManager::class,
            CreditsRelationManager::class,
            LettersRelationManager::class,
            LawsuitsRelationManager::class,
            RelationGroup::make('Zlecenia', [
                DealsRelationManager::class,
                PaymentsRelationManager::class
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCHFPaymentMatters::route('/'),
            'create' => CreateCHFPaymentMatter::route('/create'),
            'edit' => EditCHFPaymentMatter::route('/{record}/edit'),
        ];
    }
}
