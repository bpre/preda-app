<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Support\FilamentContentLayout;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserPreferences extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.user-preferences';

    protected static ?string $slug = 'preferencje-uzytkownika';

    protected static ?string $title = 'Preferencje użytkownika';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(FilamentContentLayout::preferences($this->user()));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Układ')
                    ->schema([
                        Toggle::make('content_full_width')
                            ->label('Pełna szerokość całego panelu')
                            ->inline(false)
                            ->live(),

                        Select::make('content_max_width')
                            ->label('Maksymalna szerokość wrappera')
                            ->options(FilamentContentLayout::contentMaxWidthOptions())
                            ->disabled(fn (Get $get): bool => (bool) $get('content_full_width'))
                            ->native(false)
                            ->required(),

                        Select::make('content_alignment')
                            ->label('Wyrównanie wrappera')
                            ->options([
                                'left' => 'Do lewej',
                                'center' => 'Wyśrodkowane',
                            ])
                            ->native(false)
                            ->required(),
                    ])
                    ->columns([
                        'default' => 1,
                        'lg' => 3,
                    ]),

                Section::make('Tabele')
                    ->schema([
                        Toggle::make('record_list_pages_full_width')
                            ->label('Tabele rekordów pełnej szerokości')
                            ->inline(false),

                        Toggle::make('record_list_pages_full_width_toggle')
                            ->label('Przełącznik szerokości w tabelach')
                            ->inline(false),
                    ])
                    ->columns([
                        'default' => 1,
                        'lg' => 3,
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('restoreDefaults')
                ->label('Przywróć ustawienia domyślne')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Przywrócić ustawienia domyślne?')
                ->action(function (): void {
                    $this->restoreDefaults();
                }),
        ];
    }

    public function save(): void
    {
        $user = $this->user();

        abort_unless($user instanceof User, 403);

        $preferences = FilamentContentLayout::savePreferences($user, [
            ...FilamentContentLayout::preferences($user),
            ...$this->form->getState(),
        ]);

        $this->applyPreferences($preferences);

        Notification::make()
            ->success()
            ->title('Zapisano preferencje użytkownika.')
            ->send();
    }

    public function restoreDefaults(): void
    {
        $user = $this->user();

        abort_unless($user instanceof User, 403);

        $preferences = FilamentContentLayout::savePreferences($user, [
            ...FilamentContentLayout::preferences($user),
            ...$this->defaultPreferences(),
        ]);

        $this->applyPreferences($preferences);

        Notification::make()
            ->success()
            ->title('Przywrócono ustawienia domyślne.')
            ->send();
    }

    protected function applyPreferences(array $preferences): void
    {
        $this->form->fill($preferences);
        $this->dispatch('filament-layout-preferences-updated',
            contentAlignment: $preferences['content_alignment'],
            contentFullWidth: $preferences['content_full_width'],
            contentMaxWidthCssValue: FilamentContentLayout::contentMaxWidthCssValue($preferences['content_max_width']),
            recordListPagesFullWidthToggle: $preferences['record_list_pages_full_width_toggle'],
            tableWidthMode: $preferences['record_list_pages_full_width'] ? 'full' : 'contained',
            storageKey: $preferences['record_list_pages_full_width_storage_key'],
        );
    }

    protected function defaultPreferences(): array
    {
        return [
            'content_full_width' => false,
            'content_max_width' => '7xl',
            'content_alignment' => 'left',
            'record_list_pages_full_width' => true,
            'record_list_pages_full_width_toggle' => true,
        ];
    }

    protected function user(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }
}
