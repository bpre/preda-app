<?php

namespace App\Filament\Crm\Resources;

use App\Filament\Crm\Resources\CrmMailPlaceholderResource\Pages\ListCrmMailPlaceholders;
use App\Models\CrmMailPlaceholder;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class CrmMailPlaceholderResource extends Resource
{
    protected static ?string $model = CrmMailPlaceholder::class;

    protected static ?int $navigationSort = 25;

    protected static ?string $slug = 'placeholdery-maili';

    protected static ?string $navigationLabel = 'Placeholdery maili';

    protected static ?string $modelLabel = 'Placeholder maila';

    protected static ?string $pluralModelLabel = 'Placeholdery maili';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-code-bracket';

    protected static string|\UnitEnum|null $navigationGroup = 'Administracja';

    protected static ?string $navigationParentItem = 'Szablony maili';

    public static function canViewAny(): bool
    {
        return self::userCan('view_any_crm_mail_placeholder');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return self::userCan('update_crm_mail_placeholder');
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
                Section::make('Placeholder')
                    ->schema([
                        Placeholder::make('placeholder_code')
                            ->label('Kod')
                            ->content(fn (?CrmMailPlaceholder $record): HtmlString => new HtmlString(
                                '<code>'.e(CrmMailPlaceholder::placeholderForKey($record?->key)).'</code>',
                            )),
                        Toggle::make('is_active')
                            ->label('Aktywny?')
                            ->inline(false),
                        TextInput::make('name')
                            ->label('Nazwa')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                        Placeholder::make('description_info')
                            ->label('Opis')
                            ->content(fn (?CrmMailPlaceholder $record): string => (string) $record?->description)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Treści')
                    ->schema(self::variantFields())
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa')
                    ->weight(FontWeight::Bold),
                TextColumn::make('key')
                    ->label('Placeholder')
                    ->formatStateUsing(fn (?string $state): string => CrmMailPlaceholder::placeholderForKey($state))
                    ->copyable()
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Aktywny?')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Zmieniono')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
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
            'index' => ListCrmMailPlaceholders::route('/'),
        ];
    }

    /**
     * @return array<int, Textarea>
     */
    private static function variantFields(): array
    {
        $fields = [];

        foreach (CrmMailPlaceholder::DEFINITIONS as $key => $definition) {
            foreach ($definition['variants'] as $variantKey => $variant) {
                $fields[] = Textarea::make("variants.{$variantKey}")
                    ->label($variant['label'])
                    ->rows(4)
                    ->autosize()
                    ->helperText(self::variablesInfo($variant['variables']))
                    ->visible(fn (?CrmMailPlaceholder $record): bool => $record?->key === $key)
                    ->dehydrated(fn (?CrmMailPlaceholder $record): bool => $record?->key === $key)
                    ->columnSpanFull();
            }
        }

        return $fields;
    }

    /**
     * @param  array<string, string>  $variables
     */
    private static function variablesInfo(array $variables): ?HtmlString
    {
        if ($variables === []) {
            return null;
        }

        $items = collect($variables)
            ->map(fn (string $description, string $variable): string => '<li><code>'.e($variable).'</code> - '.e($description).'</li>')
            ->implode('');

        return new HtmlString('<div>Dostępne zmienne:<ul class="mt-1 pl-5 list-disc">'.$items.'</ul></div>');
    }

    private static function userCan(string $permission): bool
    {
        return auth()->user()?->can($permission) === true;
    }
}
