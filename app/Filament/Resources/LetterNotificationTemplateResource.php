<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterNotificationTemplateResource\Pages;
use App\Filament\Resources\LetterNotificationTemplateResource\Pages\ListLetterNotificationTemplates;
use App\Models\LetterNotificationTemplate;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class LetterNotificationTemplateResource extends Resource
{
    protected static ?string $model = LetterNotificationTemplate::class;

    protected static ?string $slug = 'szablony-powiadomien';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Szablony powiadomień';

    protected static ?string $modelLabel = 'Szablon powiadomienia';

    protected static ?string $pluralModelLabel = 'Szablony powiadomień';

    protected static ?string $navigationParentItem = 'Korespondencja';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return static::templateForm($schema);
    }

    public static function templateForm(Schema $schema, ?string $lockedLetterType = null): Schema
    {
        return $schema
            ->components(static::getFormSchema($lockedLetterType))
            ->columns(2);
    }

    public static function getFormSchema(?string $lockedLetterType = null): array
    {
        return [
            TextInput::make('name')
                ->label('Nazwa szablonu')
                ->live(onBlur: true)
                ->afterStateUpdated(function (?string $state, Get $get, callable $set, ?LetterNotificationTemplate $record): void {
                    if ($record?->exists) {
                        return;
                    }

                    if (filled($get('subject'))) {
                        return;
                    }

                    if (! filled($state)) {
                        return;
                    }

                    $set('subject', $state);
                })
                ->required()
                ->maxLength(255),

            Select::make('letter_type')
                ->label('Typ pisma')
                ->options(LetterNotificationTemplate::LETTER_TYPES)
                ->required()
                ->native(false)
                ->default($lockedLetterType)
                ->disabled(filled($lockedLetterType))
                ->dehydrated(),

            Section::make('Dostępne placeholdery')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Placeholder::make('available_placeholders')
                        ->label('Lista placeholderów')
                        ->content(function () {
                            $items = collect(LetterNotificationTemplate::AVAILABLE_PLACEHOLDERS)
                                ->map(fn ($description, $placeholder) => '<li><code>'.e($placeholder).'</code> - '.e($description).'</li>')
                                ->implode('');

                            return new HtmlString('<ul class="pl-5 space-y-1 text-sm list-disc">'.$items.'</ul>');
                        }),
                ])
                ->columnSpanFull(),

            TextInput::make('subject')
                ->label('Temat wiadomości')
                ->required()
                ->maxLength(255),

            Textarea::make('message')
                ->label('Treść wiadomości')
                ->required()
                ->rows(12)
                ->columnSpanFull(),

            Toggle::make('is_active')
                ->inline(false)
                ->label('Aktywny')
                ->default(true),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('letter_type')
                    ->label('Typ pisma')
                    ->formatStateUsing(fn (string $state) => LetterNotificationTemplate::LETTER_TYPES[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'danger',
                        'out' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('subject')
                    ->label('Temat')
                    ->searchable()
                    ->limit(50),

                IconColumn::make('is_active')
                    ->label('Aktywny')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Zmieniono')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('letter_type')
                    ->label('Typ pisma')
                    ->options(LetterNotificationTemplate::LETTER_TYPES),

                TernaryFilter::make('is_active')
                    ->label('Aktywny'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('name')
            ->paginated(false);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLetterNotificationTemplates::route('/'),
            // 'create' => Pages\CreateLetterNotificationTemplate::route('/create'),
            // 'edit' => Pages\EditLetterNotificationTemplate::route('/{record}/edit'),
        ];
    }
}
