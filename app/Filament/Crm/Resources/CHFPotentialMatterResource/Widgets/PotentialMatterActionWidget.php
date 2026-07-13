<?php

namespace App\Filament\Crm\Resources\CHFPotentialMatterResource\Widgets;

use App\Models\Matter;
use App\Models\MatterGeneratedDocument;
use App\Services\Crm\PotentialMatterClientActionService;
use App\Support\StageManager;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;
use RuntimeException;

class PotentialMatterActionWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?Matter $record = null;

    public ?array $data = [];

    protected string $view = 'filament.crm.resources.chf-potential-matter-resource.widgets.potential-matter-action-widget';

    protected int|string|array $columnSpan = 'full';

    public function mount(): void
    {
        $this->form->fill([
            'selectedAction' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('selectedAction')
                    ->label('Działanie')
                    ->hiddenLabel()
                    ->native(false)
                    ->placeholder('Wybierz działanie')
                    ->options(fn (): array => $this->availableActions())
                    ->live()
                    ->extraInputAttributes([
                        'aria-label' => 'Wybierz działanie',
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * @return array<string, string>
     */
    public function availableActions(): array
    {
        return app(PotentialMatterClientActionService::class)->options();
    }

    public function shouldDisplay(): bool
    {
        if (! $this->record) {
            return false;
        }

        $defaultStage = StageManager::defaultTemplateStageForMatter($this->record);

        return $defaultStage
            && $this->record->current_template_stage_id === $defaultStage->getKey();
    }

    public function sendClientMessageAction(): Action
    {
        return Action::make('sendClientMessage')
            ->label('Przygotuj maila')
            ->color('primary')
            ->disabled(fn (): bool => ! $this->selectedActionIsValid() || ! $this->shouldDisplay())
            ->modalHeading(fn (): string => $this->selectedActionIsValid()
                ? app(PotentialMatterClientActionService::class)->label($this->selectedAction())
                : 'Mail do klienta')
            ->modalDescription('Treść możesz zmodyfikować przed wysyłką.')
            ->modalSubmitActionLabel('Wyślij')
            ->modalCancelActionLabel('Anuluj')
            ->modalWidth('4xl')
            ->slideOver()
            ->fillForm(function (): array {
                if (! $this->record || ! $this->selectedActionIsValid()) {
                    return [
                        'recipient' => '-',
                        'subject' => '',
                        'body' => '',
                        'generated_document_ids' => [],
                    ];
                }

                $service = app(PotentialMatterClientActionService::class);

                return [
                    'recipient' => $service->recipientSummary($this->record) ?? '-',
                    'generated_document_ids' => [],
                    ...$service->defaultPayload($this->record, $this->selectedAction()),
                ];
            })
            ->schema([
                Placeholder::make('recipient')
                    ->label('Do')
                    ->content(fn (): string => app(PotentialMatterClientActionService::class)
                        ->recipientSummary($this->record) ?? '-'),
                TextInput::make('subject')
                    ->label('Temat')
                    ->required()
                    ->copyable(copyMessage: 'Skopiowano temat', copyMessageDuration: 1500),
                RichEditor::make('body')
                    ->label('Treść maila')
                    ->required()
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'link'],
                        ['bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ])
                    ->columnSpanFull(),
                CheckboxList::make('generated_document_ids')
                    ->label('Załączniki')
                    ->options(fn (): array => $this->generatedDocumentOptions())
                    ->visible(fn (): bool => filled($this->generatedDocumentOptions()))
                    ->allowHtml()
                    ->columns(1)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                if (! $this->record || ! $this->selectedActionIsValid()) {
                    FilamentNotification::make()
                        ->danger()
                        ->title('Nie wybrano działania')
                        ->send();

                    return;
                }

                try {
                    $stage = app(PotentialMatterClientActionService::class)->send(
                        matter: $this->record,
                        action: $this->selectedAction(),
                        subject: $data['subject'] ?? null,
                        body: $data['body'] ?? null,
                        generatedDocumentIds: $data['generated_document_ids'] ?? [],
                    );
                } catch (InvalidArgumentException|RuntimeException $exception) {
                    FilamentNotification::make()
                        ->danger()
                        ->title('Nie wysłano maila')
                        ->body($exception->getMessage())
                        ->send();

                    return;
                }

                $this->record = $this->record->refresh();
                $this->form->fill([
                    'selectedAction' => null,
                ]);

                FilamentNotification::make()
                    ->success()
                    ->title('Mail został wysłany')
                    ->body('Aktualny etap zmieniono na: '.$stage->label.'.')
                    ->send();
            });
    }

    private function selectedActionIsValid(): bool
    {
        return filled($this->selectedAction())
            && array_key_exists($this->selectedAction(), $this->availableActions());
    }

    private function selectedAction(): ?string
    {
        return $this->data['selectedAction'] ?? null;
    }

    /**
     * @return array<string, HtmlString>
     */
    private function generatedDocumentOptions(): array
    {
        if (! $this->record) {
            return [];
        }

        return $this->record
            ->generatedDocuments()
            ->orderByDesc('generated_at')
            ->orderByDesc('created_at')
            ->get()
            ->mapWithKeys(fn ($document): array => [
                $document->getKey() => $this->generatedDocumentOptionLabel($document),
            ])
            ->all();
    }

    private function generatedDocumentOptionLabel(MatterGeneratedDocument $document): HtmlString
    {
        $filename = e($document->downloadFilename());
        $previewUrl = e(route('matter-generated-documents.preview', $document));

        return new HtmlString(<<<HTML
            <span style="display: inline-flex; align-items: center; gap: 0.45rem;">
                <span>{$filename}</span>
                <a
                    href="{$previewUrl}"
                    target="_blank"
                    rel="noopener noreferrer"
                    title="Otwórz plik w nowej karcie"
                    aria-label="Otwórz plik w nowej karcie"
                    onclick="event.stopPropagation();"
                    onmousedown="event.stopPropagation();"
                    style="display: inline-flex; align-items: center; color: rgb(75 85 99);"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 3h6v6"></path>
                        <path d="M10 14 21 3"></path>
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    </svg>
                </a>
            </span>
        HTML);
    }
}
