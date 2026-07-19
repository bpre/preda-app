<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\Pages\ViewRole;
use App\Support\ShieldPanelPermissions;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource as ShieldRoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;

class RoleResource extends ShieldRoleResource
{
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make('Rola')
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('filament-shield::filament-shield.field.name'))
                                    ->unique(
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule): Unique => Utils::isTenancyEnabled()
                                            ? $rule->where(Utils::getTenantModelForeignKey(), Filament::getTenant()?->id)
                                            : $rule
                                    )
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('guard_name')
                                    ->label(__('filament-shield::filament-shield.field.guard_name'))
                                    ->default(Utils::getFilamentAuthGuard())
                                    ->nullable()
                                    ->maxLength(255),

                                Select::make(config('permission.column_names.team_foreign_key'))
                                    ->label(__('filament-shield::filament-shield.field.team'))
                                    ->placeholder(__('filament-shield::filament-shield.field.team.placeholder'))
                                    ->default(Filament::getTenant()?->id)
                                    ->options(fn (): array => in_array(Utils::getTenantModel(), [null, '', '0'], true) ? [] : Utils::getTenantModel()::pluck('name', 'id')->toArray())
                                    ->visible(fn (): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled())
                                    ->dehydrated(fn (): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled()),

                                static::getSelectAllFormComponent(),
                            ])
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                static::getPanelAwareShieldFormComponents(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }

    protected static function getPanelAwareShieldFormComponents(): Component
    {
        return Tabs::make('Uprawnienia')
            ->contained()
            ->tabs(
                collect(ShieldPanelPermissions::groups())
                    ->map(fn (array $group): Tab => Tab::make($group['id'])
                        ->label($group['label'])
                        ->badge($group['count'])
                        ->schema(static::getPanelTabSchema($group)))
                    ->values()
                    ->all()
            )
            ->columnSpanFull();
    }

    protected static function getPanelTabSchema(array $group): array
    {
        $schema = [];

        if ($group['resources'] !== []) {
            $schema[] = Section::make('Zasoby')
                ->schema([
                    Grid::make()
                        ->schema(
                            collect($group['resources'])
                                ->map(fn (array $resource): Section => static::getResourcePermissionSection($resource))
                                ->all()
                        )
                        ->columns(static::shield()->getGridColumns()),
                ])
                ->collapsible();
        }

        if ($group['pages'] !== []) {
            $schema[] = Section::make('Strony')
                ->schema([
                    static::getCheckboxListFormComponent(
                        name: "{$group['id']}_pages_tab",
                        options: $group['pages'],
                    ),
                ])
                ->collapsible();
        }

        if ($group['widgets'] !== []) {
            $schema[] = Section::make('Widgety')
                ->schema([
                    static::getCheckboxListFormComponent(
                        name: "{$group['id']}_widgets_tab",
                        options: $group['widgets'],
                    ),
                ])
                ->collapsible();
        }

        if (($group['customPermissions'] ?? []) !== []) {
            $schema[] = Section::make(static::getCustomPermissionsSectionLabel($group))
                ->schema([
                    static::getCheckboxListFormComponent(
                        name: "{$group['id']}_custom_permissions_tab",
                        options: $group['customPermissions'],
                    ),
                ])
                ->collapsible();
        }

        return $schema;
    }

    protected static function getCustomPermissionsSectionLabel(array $group): string
    {
        return match ($group['id']) {
            'crm' => 'Dostęp marketingowy',
            default => 'Narzędzia administracyjne',
        };
    }

    protected static function getResourcePermissionSection(array $resource): Section
    {
        return Section::make($resource['label'])
            ->description(fn (): HtmlString => new HtmlString('<span style="word-break: break-word;">'.Utils::showModelPath($resource['modelFqcn']).'</span>'))
            ->compact()
            ->schema([
                static::getCheckboxListFormComponent(
                    name: $resource['resourceFqcn'],
                    options: $resource['permissionOptions'],
                    searchable: false,
                    columns: static::shield()->getResourceCheckboxListColumns(),
                    columnSpan: static::shield()->getResourceCheckboxListColumnSpan(),
                ),
            ])
            ->columnSpan(static::shield()->getSectionColumnSpan())
            ->collapsible();
    }
}
