<?php

namespace App\Livewire;

use App\Support\PanelRegistry;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PanelSwitcher extends Component implements HasForms
{
    use InteractsWithForms;

    public array $panels = [];

    public ?array $data = [];

    public function mount(): void
    {
        $this->panels = PanelRegistry::availableFor(auth()->user());

        $this->form->fill([
            'panel' => Filament::getCurrentPanel()?->getId(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('panel')
                    ->label('Przełącz panel')
                    ->hiddenLabel()
                    ->native(false)
                    ->options(fn (): array => collect($this->panels)
                        ->mapWithKeys(fn (array $panel): array => [$panel['id'] => $panel['label']])
                        ->all())
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateUpdated(function (?string $state): void {
                        if (blank($state) || $state === Filament::getCurrentPanel()?->getId()) {
                            return;
                        }

                        $url = collect($this->panels)->firstWhere('id', $state)['url'] ?? null;

                        if (filled($url)) {
                            $this->redirect($url);
                        }
                    })
                    ->extraInputAttributes([
                        'aria-label' => 'Przełącz panel',
                    ]),
            ])
            ->statePath('data');
    }

    public function render(): View
    {
        return view('livewire.panel-switcher');
    }
}
