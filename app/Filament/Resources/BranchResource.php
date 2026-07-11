<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages\CreateBranch;
use App\Filament\Resources\BranchResource\Pages\EditBranch;
use App\Filament\Resources\BranchResource\Pages\ListBranches;
use App\Filament\Resources\BranchResource\Pages\RaportBranch;
use App\Filament\Resources\BranchResource\Pages\ViewBranch;
use App\Filament\Resources\BranchResource\RelationManagers\ExpensesRelationManager;
use App\Filament\Resources\BranchResource\RelationManagers\MattersRelationManager;
use App\Filament\Resources\BranchResource\RelationManagers\PaymentsRelationManager;
use App\Models\Branch;
use App\Models\User;
use App\Support\Branches\BranchReport;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $slug = 'oddzialy';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $modelLabel = 'Oddział';

    protected static ?string $pluralModelLabel = 'Oddziały';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Zarządzanie';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }

    public static function canViewFinances(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }

    public static function closeBranchAction(): Action
    {
        return Action::make('closeBranch')
            ->label('Zamknij dla nowych spraw')
            ->icon('heroicon-o-lock-closed')
            ->color('warning')
            ->requiresConfirmation()
            ->form([
                DatePicker::make('closed_at')
                    ->label('Data zamknięcia')
                    ->default(now())
                    ->required(),
            ])
            ->action(function (Branch $record, array $data): void {
                $record->closeForNewMatters($data['closed_at']);
            })
            ->visible(fn (Branch $record): bool => $record->acceptsNewMatters());
    }

    public static function reopenBranchAction(): Action
    {
        return Action::make('reopenBranch')
            ->label('Otwórz dla nowych spraw')
            ->icon('heroicon-o-lock-open')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (Branch $record): void {
                $record->reopenForNewMatters();
            })
            ->visible(fn (Branch $record): bool => ! $record->acceptsNewMatters());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dane oddziału')
                    ->schema([
                        TextInput::make('label')
                            ->label('Nazwa oddziału')
                            ->required(),
                        Select::make('type')
                            ->label('Typ')
                            ->options(Branch::typeOptions())
                            ->default(Branch::TYPE_STATIONARY)
                            ->required()
                            ->native(false),
                        Select::make('user_id')
                            ->label('Osoba kierująca oddziałem')
                            ->searchable()
                            ->options(fn () => User::responsible_lawyers()->pluck('name', 'id'))
                            ->required()
                            ->native(false),
                        Toggle::make('accepts_new_matters')
                            ->label('Przyjmuje nowe sprawy')
                            ->default(true)
                            ->live()
                            ->afterStateUpdated(function (Set $set, bool $state): void {
                                if ($state) {
                                    $set('closed_at', null);

                                    return;
                                }

                                $set('closed_at', now()->toDateString());
                                $set('is_default_for_new_matters', false);
                            }),
                        DatePicker::make('closed_at')
                            ->label('Data zamknięcia')
                            ->visible(fn (Get $get): bool => ! (bool) $get('accepts_new_matters')),
                        Toggle::make('is_default_for_new_matters')
                            ->label('Domyślny dla nowych spraw')
                            ->disabled(fn (Get $get): bool => ! (bool) $get('accepts_new_matters'))
                            ->dehydrated(),
                    ])
                    ->columns(2),
                Section::make('Adres i kontakt')
                    ->schema([
                        TextInput::make('street')
                            ->label('Ulica i numer')
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->label('Kod pocztowy')
                            ->maxLength(20),
                        TextInput::make('city')
                            ->label('Miejscowość')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Cele')
                    ->schema([
                        TextInput::make('monthly_matter_goal')
                            ->label('Miesięczny cel spraw')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('monthly_revenue_goal')
                            ->label('Miesięczny cel przychodu')
                            ->numeric()
                            ->prefix('PLN')
                            ->minValue(0),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Oddział')
                    ->schema([
                        TextEntry::make('label')->label('Nazwa')->weight(FontWeight::Bold),
                        TextEntry::make('type')
                            ->label('Typ')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => Branch::typeOptions()[$state] ?? '-')
                            ->color(fn (?string $state): string => $state === Branch::TYPE_REMOTE ? 'info' : 'gray'),
                        TextEntry::make('director.name')->label('Osoba kierująca')->placeholder('-'),
                        TextEntry::make('accepts_new_matters')
                            ->label('Przyjmuje nowe sprawy')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'tak' : 'nie')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('closed_at')->label('Data zamknięcia')->date()->placeholder('-'),
                        TextEntry::make('is_default_for_new_matters')
                            ->label('Domyślny')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'tak' : 'nie')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                        TextEntry::make('address')
                            ->label('Adres')
                            ->getStateUsing(fn (Branch $record): ?string => $record->fullAddress())
                            ->placeholder('-'),
                        TextEntry::make('email')->label('E-mail')->placeholder('-'),
                        TextEntry::make('phone')->label('Telefon')->placeholder('-'),
                        TextEntry::make('monthly_matter_goal')
                            ->label('Miesięczny cel spraw')
                            ->placeholder('-'),
                        TextEntry::make('monthly_revenue_goal')
                            ->label('Miesięczny cel przychodu')
                            ->money('PLN', locale: 'pl')
                            ->placeholder('-'),
                    ])
                    ->columns(3),
                ViewEntry::make('report')
                    ->label('')
                    ->view('filament.resources.branch-resource.components.report-table')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with('director')
                ->withCount([
                    'chfMatters as chf_matters_count',
                    'activeChfMatters as active_chf_matters_count',
                ]))
            ->columns([
                TextColumn::make('label')
                    ->label('Oddział')
                    ->size(TextSize::Medium)
                    ->weight(FontWeight::Bold)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Branch::typeOptions()[$state] ?? '-')
                    ->color(fn (?string $state): string => $state === Branch::TYPE_REMOTE ? 'info' : 'gray')
                    ->sortable(),
                TextColumn::make('director.name')
                    ->label('Osoba kierująca oddziałem')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('accepts_new_matters')
                    ->label('Nowe')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('active_chf_matters_count')
                    ->label('Aktywne CHF')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('chf_matters_count')
                    ->label('Wszystkie CHF')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('monthly_matter_goal')
                    ->label('Cel spraw')
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('monthly_revenue_goal')
                    ->label('Cel przychodu')
                    ->money('PLN', locale: 'pl')
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('paid_sum')
                    ->label('Przychody')
                    ->getStateUsing(fn (Branch $record): float => BranchReport::make($record)->totals()['paid'])
                    ->money('PLN', locale: 'pl')
                    ->alignEnd()
                    ->toggleable(),
                TextColumn::make('expense_sum')
                    ->label('Wydatki')
                    ->getStateUsing(fn (Branch $record): float => BranchReport::make($record)->totals()['expense'])
                    ->money('PLN', locale: 'pl')
                    ->alignEnd()
                    ->toggleable(),
                TextColumn::make('balance')
                    ->label('Bilans')
                    ->getStateUsing(function (Branch $record): float {
                        $totals = BranchReport::make($record)->totals();

                        return $totals['paid'] - $totals['expense'];
                    })
                    ->money('PLN', locale: 'pl')
                    ->color(fn ($state): string => $state >= 0 ? 'success' : 'danger')
                    ->alignEnd()
                    ->toggleable(),
            ])
            ->recordUrl(
                fn (Branch $record): string => BranchResource::getUrl('view', ['record' => $record]),
            )
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                static::closeBranchAction()->iconButton(),
                static::reopenBranchAction()->iconButton(),
                EditAction::make()->iconButton(),
                DeleteAction::make()
                    ->hidden(fn (Branch $record): bool => $record->hasAnyRelation())
                    ->iconButton(),
            ])
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [
            MattersRelationManager::class,
            PaymentsRelationManager::class,
            ExpensesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBranches::route('/'),
            'create' => CreateBranch::route('/create'),
            'view' => ViewBranch::route('/{record}'),
            'edit' => EditBranch::route('/{record}/edit'),
            'raport' => RaportBranch::route('/{record}/raport'),
        ];
    }

    // public static function getRecordSubNavigation(Page $page): array
    // {
    //     return $page->generateNavigationItems([
    //         Pages\ManageBranch::class
    //     ]);
    // }

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;
}
