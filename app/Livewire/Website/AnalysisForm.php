<?php

namespace App\Livewire\Website;

use App\Models\User;
use App\Models\Website\Bank;
use App\Models\Website\Lead;
use App\Notifications\LeadDocumentsUploadedToAdmin;
use App\Notifications\NewLeadToAdmin;
use App\Notifications\NewLeadToClient;
use App\Services\Website\LeadPotentialMatterService;
use App\Support\Website\LeadAttribution;
use App\Support\Website\LeadFileNames;
use App\Support\Website\LeadTypes;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Component;

class AnalysisForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    private const FORM_WIZARD_KEY = 'analysis-form-wizard';

    private const CREDIT_INFORMATION_STEP_KEY = 'analysis-form-credit-information-step';

    private const CLIENT_DATA_STEP_KEY = 'analysis-form-client-data-step';

    private const UNKNOWN_BANK_OPTION = 'Inny / nie pamiętam';

    public ?array $data = [];

    public ?array $uploadData = [];

    public ?array $attributionData = [];

    public string $context = 'page';

    public $files = [];

    public $content = [];

    public bool $no_docs = false;

    public bool $hasContract = false;

    public bool $documentsUploaded = false;

    public bool $documentsSkipped = false;

    public ?string $leadToken = null;

    public $sent = false;

    public function mount(string $context = 'page', mixed $content = null)
    {
        $this->context = $context === 'sidebar' ? 'sidebar' : 'page';

        if ($content !== null) {
            $this->content = $content;
        }

        $this->form->fill($this->initialFormData());

    }

    public function form(Schema $schema): Schema
    {
        $creditInformationColumns = 8;
        $clientDataColumns = 4;

        return $schema
            ->components([
                Wizard::make([
                    Step::make('Informacje o kredycie')
                        ->key(self::CREDIT_INFORMATION_STEP_KEY)
                        ->schema($this->creditInformationFields())
                        ->columns($creditInformationColumns),

                    Step::make('Twoje dane')
                        ->key(self::CLIENT_DATA_STEP_KEY)
                        ->schema($this->clientDataFields())
                        ->columns($clientDataColumns),
                ])
                    ->key(self::FORM_WIZARD_KEY)
                    ->nextAction(fn (Action $action): Action => $action->label('Dalej'))
                    ->previousAction(fn (Action $action): Action => $action->label('Wstecz'))
                    ->submitAction($this->submitActionHtml())
                    ->columnSpanFull(),
            ])->columns($clientDataColumns)

            ->statePath('data')
            ->model(Lead::class);

    }

    public function uploadForm(Schema $schema): Schema
    {
        $components = [
            Placeholder::make('contract_upload_intro')
                ->hiddenLabel()
                ->content('Możesz od razu przesłać skan umowy. Nie jest to obowiązkowe, ale ułatwi i przyspieszy analizę sprawy.')
                ->extraAttributes([
                    'class' => 'text-sm leading-6 text-secondary-700',
                ])
                ->columnSpanFull(),

            Placeholder::make('documents_note')
                ->hiddenLabel()
                ->content('Jeżeli masz także regulamin lub aneksy, możesz je również dodać - analiza Twojej sprawy będzie bardziej kompletna.')
                ->extraAttributes([
                    'class' => 'text-sm leading-6 text-secondary-700',
                ])
                ->columnSpanFull(),

            FileUpload::make('files')
                ->label('Umowa kredytowa')
                ->disk('local')
                ->required()
                ->multiple()
                ->appendFiles()
                ->maxFiles(20)
                ->hint('Do 20 plików i 100 MB')
                ->maxSize(100000)
                ->acceptedFileTypes([
                    'application/pdf',
                    'image/jpeg',
                    'image/png',
                    'image/heic',
                    'image/heif',
                ])
                ->directory('umowy-do-analizy')
                ->storeFileNamesIn('files_names')
                ->columnSpanFull(),
        ];

        return $schema
            ->components([
                Section::make()
                    ->schema($components)
                    ->columnSpanFull(),
            ])
            ->columns(1)
            ->statePath('uploadData');
    }

    private function creditInformationFields(): array
    {
        return [
            Select::make('bank')
                ->label('Bank')
                ->placeholder('Wybierz')
                ->options(fn () => $this->bankOptions())
                ->getSearchResultsUsing(fn (string $search): array => $this->bankSearchResults($search))
                ->searchable()
                ->native(false)
                ->required()
                ->columnSpan(4),

            Select::make('contract_year_range')
                ->label('Rok umowy')
                ->placeholder('Wybierz')
                ->options($this->contractYearRangeOptions())
                ->native(false)
                ->required()
                ->columnSpan(4),

            Select::make('credit_currency')
                ->label('Waluta kredytu')
                ->placeholder('Wybierz')
                ->options($this->creditCurrencyOptions())
                ->native(false)
                ->required()
                ->columnSpan(4),

            Select::make('credit_amount_range')
                ->label('Kwota kredytu')
                ->placeholder('Wybierz')
                ->options($this->creditAmountRangeOptions())
                ->native(false)
                ->required()
                ->columnSpan(4),

            Select::make('credit_status')
                ->label('Status kredytu')
                ->placeholder('Wybierz')
                ->options($this->creditStatusOptions())
                ->native(false)
                ->required()
                ->columnSpan(4),

            Select::make('has_contract')
                ->label('Czy masz umowę?')
                ->placeholder('Wybierz')
                ->options([
                    '1' => 'Tak, mam',
                    '0' => 'Nie mam',
                ])
                ->native(false)
                ->required()
                ->columnSpan(4),

            Textarea::make('additional_info')
                ->label('Dodatkowe informacje')
                ->placeholder('To pole nie jest obowiązkowe. Możesz jednak już teraz przekazać nam dodatkowe informacje, które uważasz za istotne.')
                ->rows(3)
                ->autosize()
                ->columnSpanFull(),
        ];
    }

    private function clientDataFields(): array
    {
        return [
            TextInput::make('name')
                ->label('Imię i nazwisko')
                ->extraAttributes(['class' => 'z-20'])
                ->required()
                ->columnSpan(2),

            TextInput::make('phone')
                ->label('Numer telefonu')
                ->minLength(9)
                ->tel()
                ->required()
                ->columnSpan(2),

            TextInput::make('email')
                ->label('E-mail')
                ->email()
                ->required()
                ->columnSpan(3),

            TextInput::make('postal_code')
                ->label('Kod pocztowy')
                ->placeholder('00-000')
                ->mask('99-999')
                ->maxLength(6)
                ->regex('/^\d{2}-\d{3}$/')
                ->validationMessages([
                    'regex' => 'Kod pocztowy powinien mieć format 00-000.',
                ])
                ->required()
                ->columnSpan(1),

            Checkbox::make('policy')
                ->label('wyrażam zgodę na przetwarzanie danych osobowych')
                ->required()
                ->columnSpanFull(),

            Placeholder::make('No Label')
                ->hiddenLabel()
                ->extraAttributes([
                    'class' => 'text-xs text-gray-500 prose-a:text-gray-500',
                    'style' => 'line-height: 105%',
                ])
                ->content(fn () => new HtmlString('Administratorem danych osobowych jest PRĘDA Kancelaria Adwokacka - Adwokat Bartosz Pręda z siedzibą w Głogowie. Podanie danych jest dobrowolne. Masz prawo m.in. dostępu do Twoich danych, żądania ich poprawienia oraz usunięcia. Szczegóły w <a href="/polityka-prywatnosci" target="_blank">polityce prywatności</a>.'))
                ->columnSpanFull(),
        ];
    }

    private function submitActionHtml(): HtmlString
    {
        return new HtmlString(Blade::render(<<<'BLADE'
            <x-button.primary-link
                as="button"
                type="submit"
                wire:loading.attr="disabled"
                wire:target="create"
                class="disabled:pointer-events-none disabled:opacity-70 {{ $isSidebar ? 'w-full' : '' }}"
            >
                <span wire:loading.remove wire:target="create">
                    Wyślij zgłoszenie do bezpłatnej analizy
                </span>

                <span
                    wire:loading.flex
                    wire:target="create"
                    aria-live="polite"
                    style="align-items: center; gap: 0.5rem;"
                >
                    <svg
                        aria-hidden="true"
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <g>
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity="0.25" />
                            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                            <animateTransform
                                attributeName="transform"
                                type="rotate"
                                from="0 12 12"
                                to="360 12 12"
                                dur="0.8s"
                                repeatCount="indefinite"
                            />
                        </g>
                    </svg>

                    <span>Wysyłanie...</span>
                </span>
            </x-button.primary-link>
        BLADE, [
            'isSidebar' => $this->context === 'sidebar',
        ]));
    }

    public function create()
    {

        $state = $this->form->getState();
        $hasContract = (string) ($state['has_contract'] ?? '') === '1';
        $additionalInfo = trim((string) ($state['additional_info'] ?? ''));

        $state['has_contract'] = $hasContract;
        $state['upload_token'] = (string) Str::uuid();
        $state['lead_type'] = LeadTypes::FORM;
        $state['message'] = $this->buildLeadMessage($state, $additionalInfo);
        $state['additional_info'] = $additionalInfo !== '' ? $additionalInfo : null;
        $state = array_merge($state, LeadAttribution::fromPayload($this->attributionData, request()));

        $user = User::where('email', 'bartosz.preda@preda.info')->first();
        $lead = Lead::create($state);

        $user?->notify(new NewLeadToAdmin($lead));

        Notification::route('mail', $lead->email)
            ->notify(new NewLeadToClient($lead));

        $this->sent = true;
        $this->hasContract = $hasContract;
        $this->no_docs = ! $hasContract;
        $this->leadToken = $lead->upload_token;
        $this->documentsUploaded = false;
        $this->documentsSkipped = false;
        $this->uploadForm->fill(['files' => []]);

        $this->dispatchAnalysisEvent('analysis_form_submitted', [
            'hasContract' => $hasContract,
            'leadStep' => 'lead_created',
        ]);
        $this->dispatchAnalysisEvent($hasContract ? 'analysis_contract_available_yes' : 'analysis_contract_available_no', [
            'hasContract' => $hasContract,
            'leadStep' => 'lead_created',
        ]);
        $this->dispatchSidebarCompletionState(! $hasContract);
    }

    public function uploadDocuments(): void
    {
        if (! $this->hasContract || blank($this->leadToken)) {
            return;
        }

        $state = $this->uploadForm->getState();
        $files = $state['files'] ?? [];
        $fileNames = LeadFileNames::mapForFiles($files, $state['files_names'] ?? []);

        $lead = $this->currentLead();

        if (! $lead) {
            return;
        }

        $lead->forceFill([
            'files' => $files,
            'files_names' => $fileNames,
            'documents_uploaded_at' => now(),
            'documents_skipped_at' => null,
        ])->save();

        app(LeadPotentialMatterService::class)->syncLeadFilesToPotentialMatter($lead);

        $user = User::where('email', 'bartosz.preda@preda.info')->first();
        $user?->notify(new LeadDocumentsUploadedToAdmin($lead));

        $this->documentsUploaded = true;
        $this->documentsSkipped = false;

        $this->dispatchAnalysisEvent('analysis_documents_uploaded', [
            'documentCount' => count($files),
            'hasContract' => true,
            'leadStep' => 'documents_uploaded',
        ]);
        $this->dispatchSidebarCompletionState(true);
    }

    public function skipDocuments(): void
    {
        $lead = $this->currentLead();

        if ($lead) {
            $lead->forceFill([
                'documents_skipped_at' => now(),
            ])->save();
        }

        $this->documentsSkipped = true;
        $this->documentsUploaded = false;

        $this->dispatchAnalysisEvent('analysis_documents_skipped', [
            'hasContract' => $this->hasContract,
            'leadStep' => 'documents_skipped',
        ]);
        $this->dispatchSidebarCompletionState(true);
    }

    public function resetAfterSidebarCompletion(): void
    {
        if (! $this->sidebarProcessIsComplete()) {
            return;
        }

        $this->resetErrorBag();
        $this->resetValidation();

        $this->data = [];
        $this->uploadData = [];
        $this->files = [];
        $this->no_docs = false;
        $this->hasContract = false;
        $this->documentsUploaded = false;
        $this->documentsSkipped = false;
        $this->leadToken = null;
        $this->sent = false;

        $this->form->fill($this->initialFormData());
        $this->uploadForm->fill(['files' => []]);
        $this->dispatchSidebarCompletionState(false);
        $this->dispatch('go-to-wizard-step', key: self::FORM_WIZARD_KEY, step: self::CREDIT_INFORMATION_STEP_KEY);
    }

    private function currentLead(): ?Lead
    {
        if (blank($this->leadToken)) {
            return null;
        }

        return Lead::query()
            ->where('upload_token', $this->leadToken)
            ->first();
    }

    private function bankOptions(): array
    {
        return Bank::query()
            ->where('is_published', true)
            ->orderBy('sort')
            ->orderBy('label')
            ->pluck('label', 'label')
            ->all() + [
                self::UNKNOWN_BANK_OPTION => self::UNKNOWN_BANK_OPTION,
            ];
    }

    private function bankSearchResults(string $search): array
    {
        $search = trim($search);

        $query = Bank::query()
            ->where('is_published', true);

        if ($search !== '') {
            $query->where('label', 'like', "%{$search}%");
        }

        return $query
            ->orderBy('sort')
            ->orderBy('label')
            ->limit(49)
            ->pluck('label', 'label')
            ->all() + [
                self::UNKNOWN_BANK_OPTION => self::UNKNOWN_BANK_OPTION,
            ];
    }

    private function contractYearRangeOptions(): array
    {
        $yearOptions = array_combine(range(2002, 2012), array_map('strval', range(2002, 2012)));

        return [
            'przed 2002' => 'przed 2002',
        ] + $yearOptions + [
            'po 2012' => 'po 2012',
            'nie pamiętam' => 'nie pamiętam',
        ];
    }

    private function creditCurrencyOptions(): array
    {
        return [
            'CHF' => 'CHF',
            'EUR' => 'EUR',
            'USD' => 'USD',
            'inna' => 'inna',
            'nie wiem' => 'nie wiem',
        ];
    }

    private function creditAmountRangeOptions(): array
    {
        return [
            'poniżej 85.000 PLN' => 'poniżej 85.000 PLN',
            'od 85.000 do 300.000 PLN' => 'od 85.000 do 300.000 PLN',
            'powyżej 300.000 PLN' => 'powyżej 300.000 PLN',
        ];
    }

    private function creditStatusOptions(): array
    {
        return [
            'nadal spłacam' => 'nadal spłacam',
            'kredyt spłacony' => 'kredyt spłacony',
            'nie wiem / chcę skonsultować' => 'nie wiem / chcę skonsultować',
        ];
    }

    private function initialFormData(): array
    {
        $query = request()->query();

        return [
            'bank' => $this->resolveBankPrefill($query['b'] ?? null),
            'contract_year_range' => $this->resolveContractYearRangePrefill($query['d'] ?? null),
        ];
    }

    private function sidebarProcessIsComplete(): bool
    {
        return $this->context === 'sidebar'
            && $this->sent
            && (! $this->hasContract || $this->documentsUploaded || $this->documentsSkipped);
    }

    private function dispatchSidebarCompletionState(bool $complete): void
    {
        if ($this->context !== 'sidebar') {
            return;
        }

        $this->dispatch('analysis-form-completion', complete: $complete, context: $this->context);
    }

    private function resolveBankPrefill(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = (string) $value;

        return Bank::query()
            ->where('is_published', true)
            ->where(function ($query) use ($value) {
                if (ctype_digit($value)) {
                    $query->where('id', (int) $value);
                }

                $query->orWhere('slug', $value)
                    ->orWhere('label', $value);
            })
            ->value('label') ?? $value;
    }

    private function resolveContractYearRangePrefill(mixed $value): ?string
    {
        if (blank($value) || ! preg_match('/\d{4}/', (string) $value, $matches)) {
            return null;
        }

        $year = (int) $matches[0];

        return match (true) {
            $year < 2002 => 'przed 2002',
            $year <= 2012 => (string) $year,
            default => 'po 2012',
        };
    }

    private function buildLeadMessage(array $state, string $additionalInfo = ''): string
    {
        return 'Zgłoszenie do bezpłatnej analizy kredytu.';
    }

    private function dispatchAnalysisEvent(string $eventName, array $payload = []): void
    {
        $this->dispatch('analysis-form-event', ...array_merge([
            'eventName' => $eventName,
            'formLocation' => $this->context,
        ], $payload));
    }

    public function render()
    {
        if (request()->segment(2) == 'wyslano') {
            return view('livewire.form-analysis-sent')->layout('components.layouts.form');
        } else {
            return view('livewire.website.analysis-form');
        }
    }
}
