<?php

namespace App\Filament\Portal\Resources;

use App\Filament\Portal\Resources\LetterResource\Pages\ListLetters;
use App\Filament\Portal\Resources\LetterResource\Pages\ViewLetter;
use App\Models\Letter;
use App\Models\PortalUser;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LetterResource extends Resource
{
    protected static ?string $model = Letter::class;

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'korespondencja';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'Korespondencja';

    protected static ?string $modelLabel = 'Pismo';

    protected static ?string $pluralModelLabel = 'Korespondencja';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    public static function shouldSkipAuthorization(): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('matter', fn (Builder $query) => $query
                ->where('category', 'CHF')
                ->where('is_matter', true)
                ->whereHas('contactMatters', fn (Builder $query) => $query->where('contact_id', static::portalContactId())))
            ->with(['matter', 'sender', 'recipients']);
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
                Section::make('Pismo')
                    ->schema([
                        TextEntry::make('label')
                            ->label('Nazwa')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('date')
                            ->label('Data')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('type')
                            ->label('Rodzaj')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => Letter::TYP[$state] ?? '-')
                            ->color(fn (?string $state): string => $state === 'out' ? 'success' : 'danger'),
                        TextEntry::make('matter.label')
                            ->label('Sprawa')
                            ->placeholder('-'),
                        TextEntry::make('sender.label')
                            ->label('Nadawca')
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->label('Opis')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('portal_files')
                            ->label('Pliki')
                            ->html()
                            ->getStateUsing(fn (Letter $record): string => static::filesHtml($record))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Data')
                    ->date()
                    ->sortable(),
                TextColumn::make('label')
                    ->label('Pismo')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->description(fn (Letter $record): ?string => $record->matter?->label),
                TextColumn::make('type')
                    ->label('Rodzaj')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Letter::TYP[$state] ?? '-')
                    ->color(fn (?string $state): string => $state === 'out' ? 'success' : 'danger'),
                TextColumn::make('sender.label')
                    ->label('Nadawca')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('files_count')
                    ->label('Pliki')
                    ->badge()
                    ->getStateUsing(fn (Letter $record): int => count($record->files ?? [])),
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLetters::route('/'),
            'view' => ViewLetter::route('/{record}'),
        ];
    }

    private static function portalContactId(): ?string
    {
        $user = Filament::auth()->user();

        return $user instanceof PortalUser && $user->is_active
            ? $user->contact_id
            : null;
    }

    private static function filesHtml(Letter $letter): string
    {
        $files = $letter->files ?? [];

        if ($files === []) {
            return '-';
        }

        return collect($files)
            ->map(function (string $file) use ($letter): string {
                $name = e($letter->files_names[$file] ?? basename($file));
                $url = e(url('/z/'.$file));

                return "<a class=\"font-medium text-primary-600 hover:underline\" href=\"{$url}\" target=\"_blank\" rel=\"noopener\">{$name}</a>";
            })
            ->implode('<br>');
    }
}
