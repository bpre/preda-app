<?php

namespace App\Filament\Crm\Resources;

use App\Filament\Crm\Resources\CrmWorkflowRuleResource\Pages\ListCrmWorkflowRules;
use App\Models\CrmWorkflowRule;
use App\Services\Crm\PotentialMatterWorkflowService;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CrmWorkflowRuleResource extends Resource
{
    protected static ?string $model = CrmWorkflowRule::class;

    protected static ?int $navigationSort = 22;

    protected static ?string $slug = 'reguly-workflow';

    protected static ?string $navigationLabel = 'Reguły workflow';

    protected static ?string $modelLabel = 'Reguła workflow';

    protected static ?string $pluralModelLabel = 'Reguły workflow';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Administracja';

    public static function canViewAny(): bool
    {
        return self::userCan('view_any_crm_workflow_rule');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return self::userCan('update_crm_workflow_rule');
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reguła')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nazwa')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                        Select::make('trigger_stage_key')
                            ->label('Po etapie')
                            ->options(fn (): array => self::workflow()->stageOptions())
                            ->disabled()
                            ->dehydrated()
                            ->native(false),
                        Select::make('suggested_action_key')
                            ->label('Sugerowane działanie')
                            ->options(fn (): array => self::workflow()->actionOptions())
                            ->disabled()
                            ->dehydrated()
                            ->native(false),
                        TextInput::make('delay_days')
                            ->label('Po ilu dniach')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktywna?')
                            ->inline(false),
                        TextInput::make('reason')
                            ->label('Powód sugestii')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Blokujące etapy')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        CheckboxList::make('blocking_stage_keys')
                            ->label('Nie sugeruj, jeśli sprawa ma którykolwiek z tych etapów')
                            ->options(fn (): array => self::workflow()->stageOptions())
                            ->columns(2)
                            ->bulkToggleable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable(),
                TextColumn::make('trigger_stage_key')
                    ->label('Po etapie')
                    ->formatStateUsing(fn (?string $state): string => self::workflow()->stageLabel($state))
                    ->toggleable(),
                TextColumn::make('suggested_action_key')
                    ->label('Sugerowane działanie')
                    ->formatStateUsing(fn (?string $state): string => self::workflow()->actionLabel($state))
                    ->badge()
                    ->color('info'),
                TextColumn::make('delay_days')
                    ->label('Dni')
                    ->suffix(' dni')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktywna?')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktywna'),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('7xl'),
            ])
            ->defaultSort('sort')
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrmWorkflowRules::route('/'),
        ];
    }

    private static function workflow(): PotentialMatterWorkflowService
    {
        return app(PotentialMatterWorkflowService::class);
    }

    private static function userCan(string $permission): bool
    {
        return auth()->user()?->can($permission) === true;
    }
}
