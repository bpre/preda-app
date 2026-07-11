<?php

namespace App\Filament\Website\Resources\Reviews\Pages;

use Throwable;
use Carbon\Carbon;
use Filament\Actions\Action;
use App\Models\Website\Review;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use App\Models\Website\GoogleBusinessProfileConnection;
use App\Services\Integrations\GoogleBusinessProfileService;
use App\Filament\Website\Resources\Reviews\ReviewResource;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connectGoogleBusinessProfile')
                ->label(fn (): string => $this->getGoogleBusinessProfileConnectActionLabel())
                ->icon('heroicon-o-link')
                ->color(fn (): string => $this->getGoogleBusinessProfileConnectActionColor())
                ->url(route('website.integrations.google-business-profile.connect')),
            Action::make('selectGoogleBusinessProfileLocation')
                ->label('Wybierz profil Google')
                ->icon('heroicon-o-map-pin')
                ->color('gray')
                ->visible(fn (): bool => ! empty($this->getGoogleLocationOptions()))
                ->form([
                    Select::make('location_name')
                        ->label('Profil Google')
                        ->options($this->getGoogleLocationOptions())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $connection = $this->getGoogleBusinessProfileConnection();

                    if (! $connection) {
                        Notification::make()
                            ->danger()
                            ->title('Najpierw połącz Google Business Profile')
                            ->send();

                        return;
                    }

                    $selectedLocation = collect($connection->available_locations ?? [])
                        ->firstWhere('name', (string) ($data['location_name'] ?? ''));

                    if (! is_array($selectedLocation)) {
                        Notification::make()
                            ->danger()
                            ->title('Nie znaleziono wybranej lokalizacji Google')
                            ->send();

                        return;
                    }

                    $connection->fill([
                        'google_account_name' => $selectedLocation['account_name'] ?? null,
                        'google_account_label' => $selectedLocation['account_label'] ?? null,
                        'google_location_name' => $selectedLocation['name'] ?? null,
                        'google_location_title' => $selectedLocation['label'] ?? null,
                    ])->save();

                    Notification::make()
                        ->success()
                        ->title('Wybrano profil Google')
                        ->body((string) ($selectedLocation['label'] ?? 'Wybrano lokalizację Google Business Profile.'))
                        ->send();
                }),
            Action::make('refreshGoogleBusinessProfileLocations')
                ->label('Odśwież profile Google')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->visible(fn (): bool => (bool) $this->getGoogleBusinessProfileConnection()?->hasRefreshToken())
                ->action(function (): void {
                    try {
                        $connection = $this->getGoogleBusinessProfileConnection();

                        if (! $connection) {
                            throw new \RuntimeException('Najpierw połącz konto Google Business Profile.');
                        }

                        app(GoogleBusinessProfileService::class)->syncAccountAndLocationOptions($connection);

                        Notification::make()
                            ->success()
                            ->title('Lista profili Google została odświeżona')
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->danger()
                            ->title('Nie udało się odświeżyć profili Google')
                            ->body($exception->getMessage())
                            ->send();
                    }
                }),
            Action::make('syncGoogleBusinessProfileReviews')
                ->label('Synchronizuj z Google')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('success')
                ->visible(fn (): bool => (bool) $this->getGoogleBusinessProfileConnection()?->hasSelectedLocation())
                ->modalHeading('Synchronizacja opinii z Google Business Profile')
                ->modalDescription('Pobierz opinie, oceny i miniaturki użytkowników Google bezpośrednio do bazy danych strony.')
                ->form([
                    Toggle::make('is_published')
                        ->label('Opublikować zsynchronizowane opinie?')
                        ->default(true)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    try {
                        $connection = $this->getGoogleBusinessProfileConnection();

                        if (! $connection) {
                            throw new \RuntimeException('Najpierw połącz konto Google Business Profile.');
                        }

                        $result = app(GoogleBusinessProfileService::class)->syncReviews(
                            connection: $connection,
                            publish: (bool) ($data['is_published'] ?? true),
                        );

                        Notification::make()
                            ->success()
                            ->title('Synchronizacja opinii z Google zakończona')
                            ->body("Dodano: {$result['created']}, zaktualizowano: {$result['updated']}, pominięto: {$result['skipped']}.")
                            ->send();
                    } catch (Throwable $exception) {
                        $connection = $this->getGoogleBusinessProfileConnection();

                        if ($connection) {
                            $connection->forceFill([
                                'last_sync_error' => $exception->getMessage(),
                            ])->save();
                        }

                        Notification::make()
                            ->danger()
                            ->title('Synchronizacja z Google nie powiodła się')
                            ->body($exception->getMessage())
                            ->send();
                    }
                }),
            Action::make('disconnectGoogleBusinessProfile')
                ->label('Rozłącz Google')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->visible(fn (): bool => (bool) $this->getGoogleBusinessProfileConnection())
                ->requiresConfirmation()
                ->modalHeading('Rozłączyć Google Business Profile?')
                ->modalDescription('Usunięte zostanie zapisane połączenie i lista profili Google. Zaimportowane opinie pozostaną w bazie.')
                ->action(function (): void {
                    $this->getGoogleBusinessProfileConnection()?->delete();

                    Notification::make()
                        ->success()
                        ->title('Połączenie z Google zostało usunięte')
                        ->send();
                }),
            Action::make('importCsv')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Import opinii z pliku CSV')
                ->modalDescription('Zaimportuj plik CSV z WiserReview. Puste wiersze zostaną pominięte, a istniejące opinie o tym samym autorze, dacie i treści zostaną zaktualizowane.')
                ->form([
                    FileUpload::make('csv')
                        ->label('Plik CSV')
                        ->disk('local')
                        ->directory('imports/reviews')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'application/vnd.ms-excel',
                        ])
                        ->required(),
                    Toggle::make('is_published')
                        ->label('Opublikować importowane opinie?')
                        ->default(true)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $result = $this->importReviewsFromCsv(
                        csvPath: $data['csv'],
                        publish: (bool) ($data['is_published'] ?? true),
                    );

                    Notification::make()
                        ->success()
                        ->title('Import opinii zakończony')
                        ->body("Dodano: {$result['created']}, zaktualizowano: {$result['updated']}, pominięto: {$result['skipped']}.")
                        ->send();
                }),
            CreateAction::make(),
        ];
    }

    protected function getGoogleBusinessProfileConnection(): ?GoogleBusinessProfileConnection
    {
        return GoogleBusinessProfileConnection::query()->first();
    }

    protected function getGoogleBusinessProfileConnectActionLabel(): string
    {
        $connection = $this->getGoogleBusinessProfileConnection();

        if (! $connection) {
            return 'Połącz Google';
        }

        return $connection->hasRefreshToken() ? 'Połącz ponownie Google' : 'Dokończ połączenie Google';
    }

    protected function getGoogleBusinessProfileConnectActionColor(): string
    {
        $connection = $this->getGoogleBusinessProfileConnection();

        return $connection && ! $connection->hasRefreshToken() ? 'warning' : 'primary';
    }

    /**
     * @return array<string, string>
     */
    protected function getGoogleLocationOptions(): array
    {
        return collect($this->getGoogleBusinessProfileConnection()?->available_locations ?? [])
            ->mapWithKeys(function (array $location): array {
                $label = trim(implode(' | ', array_filter([
                    $location['label'] ?? null,
                    $location['account_label'] ?? null,
                ])));

                return [
                    (string) ($location['name'] ?? '') => $label !== '' ? $label : (string) ($location['name'] ?? ''),
                ];
            })
            ->filter(fn (string $label, string $name): bool => $name !== '')
            ->all();
    }

    /**
     * @return array{created:int,updated:int,skipped:int}
     */
    protected function importReviewsFromCsv(string $csvPath, bool $publish): array
    {
        $fullPath = Storage::disk('local')->path($csvPath);
        $handle = fopen($fullPath, 'r');

        if ($handle === false) {
            Notification::make()
                ->danger()
                ->title('Nie udało się otworzyć pliku CSV.')
                ->send();

            return ['created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        $header = fgetcsv($handle);

        if (! is_array($header)) {
            fclose($handle);
            Storage::disk('local')->delete($csvPath);

            Notification::make()
                ->danger()
                ->title('Plik CSV jest pusty albo ma nieprawidłowy format.')
                ->send();

            return ['created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        $header = array_map(
            fn (?string $value): string => strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $value))),
            $header
        );

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $nextSort = ((int) Review::max('sort')) ?: 0;

        DB::transaction(function () use ($handle, $header, $publish, &$created, &$updated, &$skipped, &$nextSort): void {
            while (($row = fgetcsv($handle)) !== false) {
                $mapped = $this->mapCsvRowToReviewData($header, $row, $publish);

                if ($mapped === null) {
                    $skipped++;
                    continue;
                }

                $review = Review::query()
                    ->where('name', $mapped['name'])
                    ->where('date', $mapped['date'])
                    ->where('review', $mapped['review'])
                    ->first();

                if ($review) {
                    $review->fill($mapped);
                    $review->save();
                    $updated++;

                    continue;
                }

                $nextSort++;
                $mapped['sort'] = $nextSort;

                Review::create($mapped);
                $created++;
            }
        });

        fclose($handle);
        Storage::disk('local')->delete($csvPath);

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  array<int, string>  $header
     * @param  array<int, string|null>  $row
     * @return array{name:string,date:string,amount:int,rating:int,color:string,review:string,is_published:bool,avatar_url?:string}|null
     */
    protected function mapCsvRowToReviewData(array $header, array $row, bool $publish): ?array
    {
        $row = array_pad($row, count($header), null);
        $payload = array_combine($header, array_slice($row, 0, count($header)));

        if (! is_array($payload)) {
            return null;
        }

        $title = trim((string) ($payload['review_title'] ?? ''));
        $text = trim((string) ($payload['review_text'] ?? ''));
        $reviewBody = trim(implode("\n\n", array_filter([$title, $text])));

        $name = trim((string) ($payload['reviewer_name'] ?? ''));
        $date = trim((string) ($payload['review_date'] ?? ''));
        $rawRating = trim((string) ($payload['rating'] ?? ''));
        $rating = (int) ($rawRating !== '' ? $rawRating : 5);
        $rating = max(1, min(5, $rating));
        $avatarUrl = trim((string) (
            $payload['avatar_url']
            ?? $payload['reviewer_avatar_url']
            ?? $payload['reviewer_profile_photo_url']
            ?? $payload['profile_photo_url']
            ?? ''
        ));

        $hasMeaningfulData = $reviewBody !== ''
            || $rawRating !== ''
            || $name !== ''
            || $date !== '';

        if (! $hasMeaningfulData) {
            return null;
        }

        try {
            $normalizedDate = $date !== '' ? Carbon::parse($date)->toDateString() : now()->toDateString();
        } catch (\Throwable) {
            $normalizedDate = now()->toDateString();
        }

        $mapped = [
            'name' => $name !== '' ? $name : 'Anonimowa opinia',
            'date' => $normalizedDate,
            'amount' => 1,
            'rating' => $rating,
            'color' => $this->resolveReviewColor($name),
            'review' => $reviewBody,
            'is_published' => $publish,
        ];

        if ($avatarUrl !== '') {
            $mapped['avatar_url'] = $avatarUrl;
        }

        return $mapped;
    }

    protected function resolveReviewColor(string $name): string
    {
        $palette = [
            'red',
            'orange',
            'amber',
            'yellow',
            'lime',
            'green',
            'emerald',
            'teal',
            'cyan',
            'sky',
            'blue',
            'indigo',
            'violet',
            'purple',
            'pink',
            'rose',
        ];

        $seed = sprintf('%u', crc32(mb_strtolower(trim($name) !== '' ? $name : 'anonim')));

        return $palette[((int) $seed) % count($palette)];
    }
}
