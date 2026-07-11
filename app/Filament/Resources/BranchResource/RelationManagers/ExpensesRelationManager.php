<?php

namespace App\Filament\Resources\BranchResource\RelationManagers;

use App\Models\Expense;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';

    protected static ?string $title = 'Wydatki';

    protected static ?string $navigationLabel = 'Wydatek';

    protected static ?string $modelLabel = 'Wydatek';

    protected static ?string $pluralModelLabel = 'Wydatki';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label('Opis')
                    ->required()
                    ->columnSpan(2),
                Select::make('category')
                    ->label('Kategoria')
                    ->options(Expense::categoryOptions())
                    ->default(Expense::CATEGORY_GENERAL)
                    ->required()
                    ->native(false),
                TextInput::make('amount'
                )->label('Kwota')
                    ->stripCharacters(',')
                    ->numeric()
                    ->required(),
                DatePicker::make('date')
                    ->label('Data')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('date')->label('Data'),
                TextColumn::make('label')->label('Wydatek')
                    ->searchable()
                    ->size(TextSize::Medium)
                    ->weight(FontWeight::Bold),
                TextColumn::make('category')
                    ->label('Kategoria')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Expense::categoryOptions()[$state] ?? '-')
                    ->sortable(),
                TextColumn::make('amount')->label('Kwota')
                    ->summarize([
                        Sum::make()->money('PLN', locale: 'pl'),
                    ])
                    ->money('PLN', locale: 'pl'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategoria')
                    ->options(Expense::categoryOptions())
                    ->native(false),
            ])
            ->headerActions([
                CreateAction::make()->modalWidth('xl'),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
