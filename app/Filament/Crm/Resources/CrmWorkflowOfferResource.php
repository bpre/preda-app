<?php

namespace App\Filament\Crm\Resources;

use App\Filament\Crm\Resources\CrmWorkflowOfferResource\Pages\ListCrmWorkflowOffers;
use App\Models\CrmWorkflowOffer;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CrmWorkflowOfferResource extends Resource
{
    protected static ?string $model = CrmWorkflowOffer::class;

    protected static ?int $navigationSort = 24;

    protected static ?string $slug = 'oferty-workflow';

    protected static ?string $navigationLabel = 'Oferty workflow';

    protected static ?string $modelLabel = 'Oferta workflow';

    protected static ?string $pluralModelLabel = 'Oferty workflow';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Administracja';

    public static function canViewAny(): bool
    {
        return self::userCan('view_any_crm_workflow_offer');
    }

    public static function canCreate(): bool
    {
        return self::userCan('create_crm_workflow_offer');
    }

    public static function canEdit(Model $record): bool
    {
        return self::userCan('update_crm_workflow_offer');
    }

    public static function canDelete(Model $record): bool
    {
        return self::userCan('delete_crm_workflow_offer');
    }

    public static function canDeleteAny(): bool
    {
        return self::userCan('delete_any_crm_workflow_offer');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('disk')
                    ->default('local')
                    ->dehydrated(),
                Section::make('Oferta')
                    ->schema([
                        TextInput::make('label')
                            ->label('Label wewnętrzny')
                            ->placeholder('Np. dla kredytów do 85k PLN')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        FileUpload::make('path')
                            ->label('Plik oferty')
                            ->disk('local')
                            ->directory('crm/workflow-offers')
                            ->acceptedFileTypes(['application/pdf'])
                            ->storeFileNamesIn('original_name')
                            ->downloadable()
                            ->openable()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('sort')
                            ->label('Kolejność')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Aktywna?')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Label wewnętrzny')
                    ->searchable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('original_name')
                    ->label('Plik')
                    ->placeholder('-')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Aktywna?')
                    ->boolean(),
                TextColumn::make('sort')
                    ->label('Kolejność')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Zmieniono')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktywna'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Dodaj ofertę')
                    ->slideOver()
                    ->modalWidth('3xl'),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('3xl'),
                DeleteAction::make()
                    ->hidden(fn (CrmWorkflowOffer $record): bool => $record->clientMessages()->exists()),
            ])
            ->reorderable('sort')
            ->defaultSort('sort')
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrmWorkflowOffers::route('/'),
        ];
    }

    private static function userCan(string $permission): bool
    {
        return auth()->user()?->can($permission) === true;
    }
}
