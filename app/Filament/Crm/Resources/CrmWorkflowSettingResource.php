<?php

namespace App\Filament\Crm\Resources;

use App\Filament\Crm\Resources\CrmWorkflowSettingResource\Pages\ListCrmWorkflowSettings;
use App\Models\CrmWorkflowSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CrmWorkflowSettingResource extends Resource
{
    protected static ?string $model = CrmWorkflowSetting::class;

    protected static ?int $navigationSort = 23;

    protected static ?string $slug = 'ustawienia-workflow';

    protected static ?string $navigationLabel = 'Ustawienia workflow';

    protected static ?string $modelLabel = 'Ustawienia workflow';

    protected static ?string $pluralModelLabel = 'Ustawienia workflow';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Administracja';

    protected static bool $shouldRegisterNavigation = false;

    public static function canViewAny(): bool
    {
        return self::userCan('view_any_crm_workflow_setting');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return self::userCan('update_crm_workflow_setting');
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
                Section::make('Ustawienia')
                    ->schema([
                        Placeholder::make('offers_info')
                            ->label('Oferty')
                            ->content('Pliki ofert są definiowane w sekcji „Oferty workflow”.'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa'),
                TextColumn::make('updated_at')
                    ->label('Zmieniono')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('3xl'),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrmWorkflowSettings::route('/'),
        ];
    }

    private static function userCan(string $permission): bool
    {
        return auth()->user()?->can($permission) === true;
    }
}
