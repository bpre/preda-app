<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CHFMatterResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\Pages\ListPayments;
use App\Models\Payment;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class PaymentResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'view_list',
        ];
    }

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'platnosci';

    protected static ?string $model = Payment::class;

    protected static ?string $navigationLabel = 'Płatności';

    protected static ?string $modelLabel = 'Płatność';

    protected static ?string $pluralModelLabel = 'Płatności';

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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label('Opis')
                    ->required()
                    ->columnSpan(3),
                TextInput::make('amount'
                )->label('Kwota')
                    ->stripCharacters(',')
                    ->numeric()
                    ->required()
                    ->columnSpan(3),
                DatePicker::make('deadline')
                    ->label('Termin płatności')
                    // ->required()
                    ->columnSpan(2),
                Toggle::make('is_paid')
                    ->label('Zapłacone?')
                    ->inline(false)
                    ->live()
                    ->columnSpan(2),
                DatePicker::make('date')
                    ->label('Data zapłaty')
                    ->required()
                    ->hidden(fn (Get $get) => ! $get('is_paid'))
                    ->columnSpan(2),
            ])->columns(6);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('deadline')->label('Termin')->sortable(),
                TextColumn::make('date')->label('Data zapłaty')->sortable()->placeholder('niezapłacone')
                    ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('is_paid')->label('Zapłacone?')->sortable(),
                TextColumn::make('amount')->label('Kwota')
                    ->size(TextSize::Medium)
                    ->weight(FontWeight::Bold)
                    ->color(function (Payment $record) {

                        if ($record->deadline && $record->deadline < now() && $record->is_paid == 0) {
                            return 'danger';
                        } elseif ($record->is_paid == 1) {
                            return 'success';
                        } else {
                            return 'gray';
                        }

                    })
                    ->summarize([
                        Sum::make()->money('PLN', locale: 'pl'),
                    ])
                    ->money('PLN', locale: 'pl'),
                TextColumn::make('label')->label('Opis')->searchable(),
                TextColumn::make('matter.label')->label('Sprawa')
                    ->hiddenOn(PaymentsRelationManager::class)
                    ->limit(50)->toggleable()->hiddenOn(PaymentsRelationManager::class)
                    ->url(fn (Payment $record) => MatterResource::getEditUrlForMatter($record->matter)),
            ])
            ->filters([
                TernaryFilter::make('is_paid')
                    ->label('Czy zapłacone')
                    ->placeholder('Pokaż wszystkie')
                    ->trueLabel('tak')
                    ->falseLabel('nie')
                    ->native(false),
                Filter::make('deadline')
                    ->label('Po terminie')
                    ->toggle()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->overdue();
                    }),

                DateRangeFilter::make('date')->label('Data dokonania płatności')->withIndicator()->autoApply(),
                DateRangeFilter::make('deadline')->label('Termin płatności')->withIndicator()->autoApply(),

            ])
            ->recordActions([
                EditAction::make()->iconButton()->modalHeading('Edytuj płatność'),
                DeleteAction::make()->iconButton(),
            ])->defaultSort('deadline', 'desc');
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
            'index' => ListPayments::route('/'),
            // 'create' => Pages\CreatePayment::route('/create'),
            // 'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
