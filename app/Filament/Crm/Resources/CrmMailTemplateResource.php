<?php

namespace App\Filament\Crm\Resources;

use App\Filament\Crm\Resources\CrmMailTemplateResource\Pages\ListCrmMailTemplates;
use App\Models\CrmMailTemplate;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
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
use Illuminate\Support\HtmlString;

class CrmMailTemplateResource extends Resource
{
    protected static ?string $model = CrmMailTemplate::class;

    protected static ?int $navigationSort = 21;

    protected static ?string $slug = 'szablony-maili';

    protected static ?string $navigationLabel = 'Szablony maili';

    protected static ?string $modelLabel = 'Szablon maila';

    protected static ?string $pluralModelLabel = 'Szablony maili';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|\UnitEnum|null $navigationGroup = 'Administracja';

    public static function canViewAny(): bool
    {
        return self::userCan('view_any_crm_mail_template');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return self::userCan('update_crm_mail_template');
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
                Select::make('action')
                    ->label('Akcja')
                    ->options(CrmMailTemplate::ACTION_LABELS)
                    ->native(false)
                    ->disabled()
                    ->dehydrated()
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->label('Nazwa')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Aktywny?')
                    ->inline(false),
                Section::make('Dostępne placeholdery')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Placeholder::make('available_placeholders')
                            ->label('Lista placeholderów')
                            ->content(fn (): HtmlString => self::availablePlaceholdersInfo()),
                    ])
                    ->columnSpanFull(),
                TextInput::make('subject')
                    ->label('Temat wiadomości')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                RichEditor::make('body')
                    ->label('Treść wiadomości')
                    ->required()
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'link'],
                        ['bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable(),
                TextColumn::make('action')
                    ->label('Akcja')
                    ->formatStateUsing(fn (string $state): string => CrmMailTemplate::ACTION_LABELS[$state] ?? $state)
                    ->badge()
                    ->color('info'),
                IconColumn::make('is_active')
                    ->label('Aktywny?')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Zmieniono')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktywny'),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('5xl'),
            ])
            ->defaultSort('sort')
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrmMailTemplates::route('/'),
        ];
    }

    private static function availablePlaceholdersInfo(): HtmlString
    {
        $items = collect(CrmMailTemplate::AVAILABLE_PLACEHOLDERS)
            ->map(fn (string $description, string $placeholder): string => '<li><code>'.e($placeholder).'</code> - '.e($description).'</li>')
            ->implode('');

        return new HtmlString('<ul class="pl-5 space-y-1 text-sm list-disc">'.$items.'</ul>');
    }

    private static function userCan(string $permission): bool
    {
        return auth()->user()?->can($permission) === true;
    }
}
