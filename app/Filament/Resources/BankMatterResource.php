<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use App\Filament\Resources\BankMatterResource\Pages\ListBankMatters;
use App\Filament\Resources\BankMatterResource\Pages\CreateBankMatter;
use App\Filament\Resources\BankMatterResource\Pages\EditBankMatter;
use App\Models\BankMatter;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\BankMatterResource\Pages;
use Filament\Resources\RelationManagers\RelationGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use App\Filament\Resources\CHFMatterResource\RelationManagers\DealsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LettersRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LawsuitsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\MatterResource\RelationManagers\ActivitiesRelationManager;

class BankMatterResource extends Resource
{

    public static function getGlobalSearchEloquentQuery(): EloquentBuilder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->where('is_archived', 0);
    }
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'powodztwo-banku';

    protected static ?string $model = BankMatter::class;
    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'Z powództwa banku';
    protected static ?string $modelLabel = 'Z powództwa banku';
    protected static ?string $pluralModelLabel = 'Sprawy z powództwa banku';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    // protected static bool $shouldRegisterNavigation = false;
    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $navigationParentItem = 'Sprawy CHF';

    // public static function getGloballySearchableAttributes(): array
    // {
    //     return ['label', 'lawsuits.signature'];
    // }
    // public static function getGlobalSearchEloquentQuery(): EloquentBuilder
    // {
    //     return parent::getGlobalSearchEloquentQuery()->where('is_archived', 0);
    // }

    public static function form(Schema $schema): Schema
    {
        return MatterResource::form($schema, 'Powództwo banku');
    }

    public static function table(Table $table): Table
    {
        return MatterResource::table($table, true, true)
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderByRaw('COALESCE(current_stage_sort, -1)')
                ->orderBy('label')
                ->orderBy('id'));
    }

    public static function getRelations(): array
    {
        return [
            // StagesRelationManager::class,
            // CreditsRelationManager::class,
            ActivitiesRelationManager::class,
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
            'index' => ListBankMatters::route('/'),
            'create' => CreateBankMatter::route('/create'),
            'edit' => EditBankMatter::route('/{record}/edit'),
        ];
    }

}
