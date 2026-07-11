<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\DepartamentResource\Pages\ListDepartaments;
use App\Filament\Resources\DepartamentResource\Pages\CreateDepartament;
use App\Filament\Resources\DepartamentResource\Pages\EditDepartament;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Departament;
use App\Forms\departamentForm;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\DepartamentResource\Pages;

class DepartamentResource extends Resource
{
    protected static ?string $slug = 'jednostki';
    protected static ?string $recordTitleAttribute = 'label';
    protected static ?string $navigationLabel = 'Jednostki organizacyjne';
    protected static ?string $modelLabel = 'Jednostki organizacyjne';
    protected static ?string $pluralModelLabel = 'Jednostki organizacyjne';
    protected static ?string $model = Departament::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';
    protected static bool $hasTitleCaseModelLabel = false;
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
        return $schema->components(departamentForm::form());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Nazwa')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->modalWidth('3xl')->modalHeading('Nowa jednostka organizacyjna'),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton()
            ])
            ->reorderable('sort')
            ->defaultSort('sort');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDepartaments::route('/'),
            'create' => CreateDepartament::route('/create'),
            'edit' => EditDepartament::route('/{record}/edit'),
        ];
    }
}
