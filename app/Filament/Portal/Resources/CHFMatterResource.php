<?php

namespace App\Filament\Portal\Resources;

use App\Filament\Portal\Resources\CHFMatterResource\Pages\ListCHFMatters;
use App\Filament\Portal\Resources\CHFMatterResource\Pages\ViewCHFMatter;
use App\Models\CHFMatter;
use App\Models\PortalUser;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CHFMatterResource extends Resource
{
    protected static ?string $model = CHFMatter::class;

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'sprawy';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'Sprawy';

    protected static ?string $modelLabel = 'Sprawa';

    protected static ?string $pluralModelLabel = 'Sprawy';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder-open';

    public static function shouldSkipAuthorization(): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_matter', true)
            ->whereHas('contactMatters', fn (Builder $query) => $query->where('contact_id', static::portalContactId()))
            ->with(['currentStage', 'lawyer']);
    }

    public static function canViewAny(): bool
    {
        return static::portalContactId() !== null;
    }

    public static function canView(Model $record): bool
    {
        return static::getEloquentQuery()->whereKey($record->getKey())->exists();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canReorder(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sprawa')
                    ->schema([
                        TextEntry::make('label')
                            ->label('Nazwa')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('currentStage.parent')
                            ->label('Etap')
                            ->placeholder('-'),
                        TextEntry::make('currentStage.label')
                            ->label('Status')
                            ->placeholder('-'),
                        TextEntry::make('lawyer.name')
                            ->label('Referat')
                            ->placeholder('-'),
                        TextEntry::make('start')
                            ->label('Data rozpoczęcia')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('end')
                            ->label('Data zakończenia')
                            ->date()
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Sprawa')
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Medium)
                    ->searchable(),
                TextColumn::make('currentStage.parent')
                    ->label('Etap')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('currentStage.label')
                    ->label('Status')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('lawyer.name')
                    ->label('Referat')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('start')
                    ->label('Start')
                    ->date()
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('end')
                    ->label('Koniec')
                    ->date()
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
            ])
            ->defaultSort('label');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCHFMatters::route('/'),
            'view' => ViewCHFMatter::route('/{record}'),
        ];
    }

    private static function portalContactId(): ?string
    {
        $user = Filament::auth()->user();

        return $user instanceof PortalUser && $user->is_active
            ? $user->contact_id
            : null;
    }
}
