<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\CreditResource\Pages\ListCredits;
use App\Filament\Resources\CreditResource\Pages\CreateCredit;
use App\Filament\Resources\CreditResource\Pages\EditCredit;
use App\Models\Credit;
use App\Models\Contact;
use Filament\Forms\Get;
use App\Forms\creditForm;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use App\Http\Controllers\PrintController;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\CreditResource\Pages;
use App\Filament\Resources\CHFMatterResource\RelationManagers\CreditsRelationManager;

class CreditResource extends Resource
{
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'umowy-kredytowe';
    protected static ?string $model = Credit::class;
    protected static ?string $navigationLabel = 'Umowy kredytowe';
    protected static ?string $modelLabel = 'Umowa kredytowa';
    protected static ?string $pluralModelLabel = 'Umowy kredytowe';
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
        return $schema->components(creditForm::form());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('former_banks.label')
                    ->label('Bank na umowie')
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Medium)
                    ->toggleable(),
                TextColumn::make('date')->label('Data na umowie')
                    ->weight(FontWeight::Bold)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('current_banks.label')
                    ->label('Bank obecnie')
                    ->toggleable(),
                TextColumn::make('number')
                    ->label('Numer')
                    ->toggleable()
                    ->searchable()
                    ->limit(30),
                TextColumn::make('matter.label')
                    ->label('Sprawa')
                    ->limit(30)
                    ->toggleable()
                    ->hiddenOn(CreditsRelationManager::class)
                    ->url(fn (Credit $record) => MatterResource::getEditUrlForMatter($record->matter))
                    ->searchable(),
                TextColumn::make('created_at')->label('Data dodania')
                ->date()
                ->toggleable(isToggledHiddenByDefault:true)
                ->sortable(),
            ])
            ->filters([
                SelectFilter::make('former_bank')
                    ->label('Bank na umowie')
                    ->relationship('former_banks', 'label')
                    ->searchable()
                    ->preload()
                    ->native(false),
                SelectFilter::make('current_bank')
                    ->label('Bank obecnie')
                    ->relationship('current_banks', 'label')
                    ->searchable()
                    ->preload()
                    ->native(false)

            ])
            ->recordActions([
                ActionGroup::make([

                    Action::make('Wniosek o zaświadczenie')
                        ->modalWidth('md')
                        ->icon('heroicon-m-folder-arrow-down')
                        ->color('gray')
                        ->hidden(fn ($record) => !$record?->id)
                        ->schema(function ($record) {


                            $record->load('contactCredit');
                            $kredytobiorcy_ids = $record->contactCredit->pluck('contact_id')->toArray();

                            // $kredytobiorcy = Contact::whereIn('id', $record->contacts)->get()->pluck('sort_name', 'id');

                            // dd($kredytobiorcy);
                            // $c = $record->contacts;
                            // array_push($c, ['kredytobiorcy' => '123']);
                            // dd($c);

                            return [

                                DatePicker::make('date')
                                    ->label('Data wniosku o wydanie zaświadczenia')
                                    ->default(now())
                                    ->required(),

                                Select::make('wnioskodawca')
                                    ->label('Wnioskodawca')
                                    ->native(false)
                                    ->options(Contact::whereIn('id', $kredytobiorcy_ids)->pluck('sort_name', 'id'))
                                    ->required('Musisz wskazać kredytobiorcę'),

                                Toggle::make('dokumenty')
                                    ->label('+ wnioskuj także o wydanie wniosku kredytowego i oświadczeń')
                                    ->default(true)
                                    ->live(),

                                Toggle::make('regulamin')
                                    ->label('+ wnioskuj także o wydanie regulaminu')
                                    ->default(false)

                            ];
                        })
                        ->action(fn ($record, $data) => PrintController::wniosekZaswiadczenie($record, $data)),
                        // ->action(function (array $data, $record) {
                        //     redirect('/print/wniosek/' . $record?->id . '?regulamin='.$data['regulamin'].'&data='.$data['data']);
                        // }),

                    Action::make('Analiza umowy')
                        ->icon('heroicon-m-folder-arrow-down')
                        ->color('gray')
                        ->hidden(fn ($record) => !$record?->id)
                        ->action(fn ($record) => PrintController::analizaUmowy($record)),

                    ])->tooltip('Generuj dokument'),

                    EditAction::make()
                        ->slideOver()
                        ->modalWidth('7xl')->iconButton()
                        ->closeModalByClickingAway(false)
                        ->modalHeading(function ($record) {
                            return 'Umowa kredytowa nr ' .$record->number;
                        })
                        ,
                    DeleteAction::make()->iconButton()->hidden(fn (Credit $record) => $record->hasAnyRelation()),
            ])->defaultSort('created_at', 'desc');
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
            'index' => ListCredits::route('/'),
            'create' => CreateCredit::route('/create'),
            'edit' => EditCredit::route('/{record}/edit'),
        ];
    }
}
