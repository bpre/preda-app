<?php

namespace App\Filament\Pages\Administration;

use App\Services\LegacyDataImportService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Throwable;

class RefreshRealData extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected string $view = 'filament.pages.administration.refresh-real-data';

    protected static ?string $navigationLabel = 'Real data';

    protected static ?string $title = 'Odśwież real data';

    protected static string|\UnitEnum|null $navigationGroup = 'Administracja';

    protected static ?int $navigationSort = 101;

    protected static ?string $slug = 'real-data';

    public string $targetSchema = '';

    public string $kancelariaSource = '';

    public string $websiteSource = '';

    public ?array $preview = null;

    public ?array $lastResult = null;

    public ?string $lastError = null;

    public function mount(): void
    {
        $importer = $this->importer();

        $this->targetSchema = $importer->targetSchema();
        $this->kancelariaSource = $importer->defaultKancelariaSource();
        $this->websiteSource = $importer->defaultWebsiteSource();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Sprawdź import')
                ->icon('heroicon-o-magnifying-glass')
                ->color('gray')
                ->fillForm(fn (): array => $this->sourceFormDefaults())
                ->form($this->sourceFormSchema())
                ->action(function (array $data): void {
                    $this->runPreview($data);
                }),

            Action::make('refresh')
                ->label('Odśwież bazę')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Odświeżyć bazę real data?')
                ->modalDescription('Ta akcja wyczyści mapowane tabele w bazie rozwijanej aplikacji i ponownie wypełni je danymi ze wskazanych lokalnych kopii.')
                ->modalSubmitActionLabel('Odśwież bazę')
                ->disabled(fn (): bool => ! $this->importer()->canRunDestructiveImport())
                ->fillForm(fn (): array => [
                    ...$this->sourceFormDefaults(),
                    'confirmation' => '',
                ])
                ->form([
                    ...$this->sourceFormSchema(),
                    TextInput::make('confirmation')
                        ->label('Potwierdzenie')
                        ->helperText("Wpisz nazwę bazy docelowej: {$this->targetSchema}")
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->runImport($data);
                }),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function previewTotals(): array
    {
        $mappings = $this->preview['mappings'] ?? [];

        return [
            'tables' => number_format(count($mappings), 0, ',', ' '),
            'source_rows' => number_format(collect($mappings)->sum(fn (array $mapping): int => (int) ($mapping['source_rows'] ?? 0)), 0, ',', ' '),
            'target_rows' => number_format(collect($mappings)->sum(fn (array $mapping): int => (int) ($mapping['target_rows'] ?? 0)), 0, ',', ' '),
            'errors' => number_format(count($this->preview['errors'] ?? []), 0, ',', ' '),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function availabilityErrors(): array
    {
        return $this->importer()->availabilityErrors(forDestructiveImport: true);
    }

    public function runPreview(array $data): void
    {
        $this->applySources($data);
        $this->targetSchema = $this->importer()->targetSchema();
        $this->lastError = null;

        try {
            $this->preview = $this->importer()->preview($this->websiteSource, $this->kancelariaSource);
        } catch (Throwable $exception) {
            $this->preview = null;
            $this->lastError = $exception->getMessage();

            Notification::make()
                ->danger()
                ->title('Nie udało się sprawdzić importu')
                ->body($exception->getMessage())
                ->send();

            return;
        }

        if (($this->preview['errors'] ?? []) !== []) {
            Notification::make()
                ->warning()
                ->title('Podgląd importu wykrył problemy')
                ->body('Import nie zostanie uruchomiony, dopóki podgląd zawiera błędy.')
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title('Podgląd importu gotowy')
            ->body('Nie wykryto błędów schematu ani konfiguracji źródeł.')
            ->send();
    }

    public function runImport(array $data): void
    {
        $this->applySources($data);
        $this->targetSchema = $this->importer()->targetSchema();
        $confirmation = trim((string) ($data['confirmation'] ?? ''));

        if ($confirmation !== $this->targetSchema) {
            Notification::make()
                ->danger()
                ->title('Potwierdzenie nie zgadza się z bazą docelową')
                ->body("Wpisz dokładnie: {$this->targetSchema}")
                ->send();

            return;
        }

        $this->lastError = null;
        $this->lastResult = null;

        try {
            @set_time_limit(0);

            $this->lastResult = $this->importer()->import($this->websiteSource, $this->kancelariaSource);
            $this->preview = $this->lastResult['after'];
        } catch (Throwable $exception) {
            $this->lastError = $exception->getMessage();

            Notification::make()
                ->danger()
                ->title('Import real data nie powiódł się')
                ->body($exception->getMessage())
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title('Baza została odświeżona')
            ->body("Tabele: {$this->lastResult['imported_tables']}. Rekordy: {$this->lastResult['imported_rows']}.")
            ->send();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(config('filament-shield.super_admin.name', 'super_admin')) === true
            && app(LegacyDataImportService::class)->canRunDestructiveImport();
    }

    /**
     * @return array<int, TextInput>
     */
    private function sourceFormSchema(): array
    {
        return [
            TextInput::make('kancelaria_source')
                ->label('Baza Kancelarii')
                ->required()
                ->maxLength(128)
                ->rule('regex:/^[A-Za-z0-9_.-]+$/'),

            TextInput::make('website_source')
                ->label('Baza strony/CMS')
                ->required()
                ->maxLength(128)
                ->rule('regex:/^[A-Za-z0-9_.-]+$/'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sourceFormDefaults(): array
    {
        return [
            'kancelaria_source' => $this->kancelariaSource,
            'website_source' => $this->websiteSource,
        ];
    }

    private function applySources(array $data): void
    {
        $this->kancelariaSource = trim((string) ($data['kancelaria_source'] ?? $this->kancelariaSource));
        $this->websiteSource = trim((string) ($data['website_source'] ?? $this->websiteSource));
    }

    private function importer(): LegacyDataImportService
    {
        return app(LegacyDataImportService::class);
    }
}
