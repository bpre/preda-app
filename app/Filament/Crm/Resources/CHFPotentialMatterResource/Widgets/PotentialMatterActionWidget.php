<?php

namespace App\Filament\Crm\Resources\CHFPotentialMatterResource\Widgets;

use App\Models\Activity;
use App\Models\CrmWorkflowOffer;
use App\Models\Matter;
use App\Models\MatterGeneratedDocument;
use App\Services\Crm\PotentialMatterClientActionService;
use App\Services\Crm\PotentialMatterNextActionService;
use App\Services\Crm\PotentialMatterWorkflowService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
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
        return ($this->record && $this->isAssignedToCurrentUser())
            ? app(PotentialMatterWorkflowService::class)->availableOptions($this->record)
            : [];
    }

    public function shouldDisplay(): bool
    {
        if (! $this->record) {
            return false;
        }

        return $this->isAssignedToCurrentUser()
            && app(PotentialMatterWorkflowService::class)->isPotentialMatter($this->record);
    }

    public function hasActionControls(): bool
    {
        return $this->hasClientMessageActions() || $this->canArchivePotentialMatter();
    }

    public function widgetHeading(): string
    {
        return $this->hasActionControls() ? 'Podejmij działanie' : 'Aktualny stan';
    }

    public function stateBadgeLabel(): string
    {
        if (! $this->record) {
            return 'Brak danych';
        }

        if ($this->record->is_archived || filled($this->record->end)) {
            return 'Zamknięta';
        }

        if (! $this->currentStageLabel()) {
            return 'Brak etapu';
        }

        if ($this->hasActionControls()) {
            return 'Do działania';
        }

        if ($this->record->next_action_due_at?->isFuture()) {
            return 'Oczekuje';
        }

        return 'Brak akcji';
    }

    public function stateBadgeStyle(): string
    {
        $base = 'border: 1px solid %s; background-color: %s; color: %s; border-radius: 999px; padding: .125rem .5rem;';

        return match ($this->stateBadgeLabel()) {
            'Do działania' => sprintf($base, 'rgb(252 165 165)', 'rgb(254 242 242)', 'rgb(185 28 28)'),
            'Brak akcji' => sprintf($base, 'rgb(134 239 172)', 'rgb(240 253 244)', 'rgb(21 128 61)'),
            'Oczekuje' => sprintf($base, 'rgb(253 230 138)', 'rgb(255 251 235)', 'rgb(180 83 9)'),
            default => sprintf($base, 'rgb(229 231 235)', 'rgb(249 250 251)', 'rgb(55 65 81)'),
        };
    }

    public function stateSummary(): string
    {
        if (! $this->record) {
            return 'Brak danych potencjalnej sprawy.';
        }

        if ($this->record->is_archived || filled($this->record->end)) {
            return 'Potencjalna sprawa jest zamknięta lub zarchiwizowana. Workflow nie przewiduje dalszych działań.';
        }

        if (! $this->currentStageLabel()) {
            return 'Brak aktualnego etapu. Ustaw etap w zakładce „Etapy”, żeby workflow mógł podpowiadać kolejne kroki.';
        }

        if ($this->canArchivePotentialMatter()) {
            return 'Po ostatnim follow-upie minął skonfigurowany czas oczekiwania. System sugeruje zamknięcie potencjalnej sprawy.';
        }

        if ($this->hasClientMessageActions()) {
            return 'Dostępne działania wynikają z aktualnego etapu i historii sprawy.';
        }

        if ($this->record->next_action_key && $this->record->next_action_due_at) {
            return 'Na teraz brak działań do wykonania. System czeka do terminu następnego sugerowanego kroku.';
        }

        return 'Workflow nie przewiduje teraz żadnego działania dla aktualnego etapu.';
    }

    /**
     * @return array<string, string>
     */
    public function stateDetails(): array
    {
        if (! $this->record) {
            return [];
        }

        $details = [
            'Aktualny etap' => $this->currentStageLabel() ?: '-',
        ];

        if ($stageDate = $this->currentStageDateLabel()) {
            $details['Data etapu'] = $stageDate;
        }

        if ($this->hasClientMessageActions()) {
            return $details;
        }

        $details['Następny krok'] = $this->record->next_action_key
            ? app(PotentialMatterWorkflowService::class)->actionLabel($this->record->next_action_key)
            : 'Brak';

        $details['Termin'] = $this->nextActionDueLabel() ?? '-';

        if (filled($this->record->next_action_reason)) {
            $details['Powód'] = (string) $this->record->next_action_reason;
        }

        return $details;
    }

    public function hasClientMessageActions(): bool
    {
        return $this->record
            && $this->isAssignedToCurrentUser()
            && app(PotentialMatterWorkflowService::class)->shouldDisplay($this->record);
    }

    public function sendClientMessageAction(): Action
    {
        return Action::make('sendClientMessage')
            ->label('Przygotuj maila')
            ->color('primary')
            ->disabled(fn (): bool => ! $this->selectedActionIsValid() || ! $this->hasClientMessageActions())
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
                        'crm_workflow_offer_id' => null,
                    ];
                }

                $service = app(PotentialMatterClientActionService::class);

                return [
                    'recipient' => $service->recipientSummary($this->record) ?? '-',
                    'generated_document_ids' => [],
                    'crm_workflow_offer_id' => $this->defaultWorkflowOfferId(),
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
                Select::make('crm_workflow_offer_id')
                    ->label('Oferta')
                    ->options(fn (): array => $this->workflowOfferOptions())
                    ->visible(fn (): bool => $this->workflowOfferOptions() !== [])
                    ->required(fn (): bool => $this->selectedAction() === PotentialMatterClientActionService::SEND_OFFER)
                    ->native(false)
                    ->searchable()
                    ->placeholder('Nie załączaj oferty')
                    ->helperText('Label oferty jest widoczny wyłącznie wewnętrznie.')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                if (! $this->record || ! $this->hasClientMessageActions() || ! $this->selectedActionIsValid()) {
                    FilamentNotification::make()
                        ->danger()
                        ->title($this->isAssignedToCurrentUser() ? 'Nie wybrano działania' : 'Brak dostępu')
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
                        sender: auth()->user(),
                        workflowOfferId: $data['crm_workflow_offer_id'] ?? null,
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

    public function archivePotentialMatterAction(): Action
    {
        return Action::make('archivePotentialMatter')
            ->label('Zamknij potencjalną sprawę')
            ->icon('heroicon-m-archive-box')
            ->color('danger')
            ->visible(fn (): bool => $this->canArchivePotentialMatter())
            ->modalHeading('Zamknąć potencjalną sprawę?')
            ->modalDescription('Sprawa zostanie oznaczona jako zamknięta i zarchiwizowana. Dodana zostanie też notatka w zakładce „Notatki i czynności”.')
            ->modalSubmitActionLabel('Zamknij sprawę')
            ->modalCancelActionLabel('Anuluj')
            ->modalWidth('lg')
            ->schema([
                DatePicker::make('closed_at')
                    ->label('Data zamknięcia')
                    ->default(now()->toDateString())
                    ->required(),
                Textarea::make('note')
                    ->label('Notatka')
                    ->placeholder('Np. Brak odpowiedzi po ostatnim follow-upie.')
                    ->rows(4),
            ])
            ->action(function (array $data): void {
                if (! $this->record || ! $this->canArchivePotentialMatter()) {
                    FilamentNotification::make()
                        ->danger()
                        ->title('Nie można zamknąć sprawy')
                        ->body('Ta potencjalna sprawa nie ma aktualnie sugestii zamknięcia.')
                        ->send();

                    return;
                }

                $closedAt = filled($data['closed_at'] ?? null)
                    ? Carbon::parse((string) $data['closed_at'])
                    : now();
                $note = trim((string) ($data['note'] ?? ''));

                $this->record->forceFill([
                    'status' => 'Zamknięta',
                    'is_archived' => true,
                    'end' => $closedAt->toDateString(),
                    'next_action_key' => null,
                    'next_action_due_at' => null,
                    'next_action_reason' => null,
                    'next_action_generated_at' => now(),
                ])->save();

                Activity::create([
                    'matter_id' => $this->record->getKey(),
                    'date' => $closedAt->toDateString(),
                    'type' => Activity::TYPE_NOTE,
                    'description' => trim("Zamknięto potencjalną sprawę po ostatnim follow-upie.\n\n".$note),
                    'created_by' => auth()->id(),
                ]);

                app(PotentialMatterNextActionService::class)->refresh($this->record->refresh());

                $this->record = $this->record->refresh();
                $this->form->fill([
                    'selectedAction' => null,
                ]);

                FilamentNotification::make()
                    ->success()
                    ->title('Potencjalna sprawa została zamknięta')
                    ->send();
            });
    }

    public function canArchivePotentialMatter(): bool
    {
        if (! $this->record) {
            return false;
        }

        $matter = $this->record->refresh();

        return app(PotentialMatterWorkflowService::class)->isPotentialMatter($matter)
            && $this->isAssignedToCurrentUser($matter)
            && ! $matter->is_archived
            && blank($matter->end)
            && $matter->next_action_key === PotentialMatterWorkflowService::ARCHIVE_POTENTIAL_MATTER
            && $matter->next_action_due_at
            && $matter->next_action_due_at->lte(now()->toDateString());
    }

    private function isAssignedToCurrentUser(?Matter $matter = null): bool
    {
        $matter ??= $this->record;
        $userId = auth()->id();

        return $matter
            && filled($matter->lawyer_id)
            && filled($userId)
            && ((string) $matter->lawyer_id === (string) $userId);
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

    private function currentStageLabel(): ?string
    {
        if (! $this->record) {
            return null;
        }

        return $this->record->currentStage?->label
            ?? $this->record->currentStage()->value('label');
    }

    private function currentStageDateLabel(): ?string
    {
        if (! $this->record) {
            return null;
        }

        $date = $this->record->currentStageRecord()->value('date');

        return $date ? Carbon::parse($date)->toDateString() : null;
    }

    private function nextActionDueLabel(): ?string
    {
        if (! $this->record?->next_action_due_at) {
            return null;
        }

        $dueAt = $this->record->next_action_due_at->copy()->startOfDay();
        $today = now()->startOfDay();
        $date = $dueAt->toDateString();

        if ($dueAt->isSameDay($today)) {
            return 'dzisiaj ('.$date.')';
        }

        if ($dueAt->lt($today)) {
            return 'po terminie od '.$date;
        }

        return $date;
    }

    private function defaultWorkflowOfferId(): ?string
    {
        if ($this->selectedAction() !== PotentialMatterClientActionService::SEND_OFFER) {
            return null;
        }

        $offerIds = array_keys($this->workflowOfferOptions());

        return $offerIds[0] ?? null;
    }

    /**
     * @return array<string, string>
     */
    private function workflowOfferOptions(): array
    {
        return CrmWorkflowOffer::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('label')
            ->get()
            ->filter(fn (CrmWorkflowOffer $offer): bool => $offer->hasFile())
            ->mapWithKeys(fn (CrmWorkflowOffer $offer): array => [$offer->getKey() => $offer->label])
            ->all();
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
