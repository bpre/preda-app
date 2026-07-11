<?php

namespace App\Filament\Website\Resources\Leads\Tables;

use ZipArchive;
use Filament\Tables\Table;
use App\Models\Website\Lead;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Enums\TextSize;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Website\Resources\Leads\LeadResource;
use App\Support\Website\LeadStatuses;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadsTable
{

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Imię i nazwisko')
                    ->searchable()
                    ->weight('bold')
                    ->size(TextSize::Medium)
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->size(TextSize::ExtraSmall)
                    ->copyable()
                    ->copyMessage('Skopiowano adres e-mail')
                    ->copyMessageDuration(1500)
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->label('Kod pocztowy')
                    ->size(TextSize::ExtraSmall)
                    ->copyable()
                    ->copyMessage('Skopiowano kod pocztowy')
                    ->copyMessageDuration(1500)
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->size(TextSize::ExtraSmall)
                    ->copyable()
                    ->copyMessage('Skopiowano numer telefonu')
                    ->copyMessageDuration(1500)
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => LeadStatuses::color($state))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('files')
                    ->label('Pliki')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->files ? count($record->files) : 'Brak plików'),
                TextColumn::make('attribution_channel')
                    ->label('Źródło')
                    ->badge()
                    ->getStateUsing(fn (Lead $record): string => $record->attribution_summary)
                    ->description(fn (Lead $record): ?string => $record->attribution_description)
                    ->color(fn (Lead $record): string => match ($record->attribution_channel) {
                        'google_ads', 'meta_ads', 'remarketing' => 'success',
                        'organic_search' => 'info',
                        'referral', 'social' => 'warning',
                        'direct' => 'gray',
                        default => 'gray',
                    })
                    ->searchable([
                        'attribution_source',
                        'attribution_medium',
                        'attribution_campaign',
                        'attribution_term',
                        'attribution_content',
                        'attribution_referrer',
                    ]),
                TextColumn::make('created_at')
                    ->label('Data zgłoszenia')
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(LeadStatuses::options()),
                SelectFilter::make('attribution_channel')
                    ->label('Źródło')
                    ->options([
                        'google_ads' => 'Google Ads',
                        'meta_ads' => 'Meta Ads',
                        'remarketing' => 'Remarketing',
                        'organic_search' => 'Wyszukiwarka organiczna',
                        'social' => 'Social media',
                        'referral' => 'Odesłanie z innej strony',
                        'direct' => 'Wejście bezpośrednie',
                        'other' => 'Inne',
                    ]),
                SelectFilter::make('attribution_campaign')
                    ->label('Kampania')
                    ->options(fn (): array => Lead::query()
                        ->whereNotNull('attribution_campaign')
                        ->orderBy('attribution_campaign')
                        ->pluck('attribution_campaign', 'attribution_campaign')
                        ->all()),
            ])
            ->recordUrl(fn ($record): string =>
                LeadResource::getUrl('view', ['record' => $record])
            )
            ->recordActions([
                Action::make('downloadFiles')
                    ->label('Pobierz pliki')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn ($record) => !empty($record->files))
                    ->action(function ($record) {
                        return self::downloadAllFiles($record);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }



    protected static function downloadAllFiles(Lead $lead): StreamedResponse|\Illuminate\Http\Response
    {
        // Wyłącz PHP warnings dla ZIP (ZIP jest wbudowane, ale php.ini ma błędną konfigurację)
        $originalErrorReporting = error_reporting();
        error_reporting($originalErrorReporting & ~E_WARNING);

        try {
            if (empty($lead->files)) {
                return response()->streamDownload(function () {
                    echo 'Brak plików do pobrania';
                }, 'brak-plikow.txt');
            }

            // Sprawdź czy ZIP jest dostępne
            if (!class_exists('ZipArchive')) {
                // Fallback - pobierz pierwszy plik
                return self::downloadFirstFile($lead);
            }

            $zipFileName = "lead-{$lead->id}-files-" . date('Y-m-d-H-i-s') . '.zip';

            return response()->streamDownload(function () use ($lead) {
                // Ponownie wyłącz warnings w closure
                error_reporting(error_reporting() & ~E_WARNING);

                // Znajdź najlepszy katalog temp
                $tempDir = self::getBestTempDirectory();
                \Log::info("Using temp directory: $tempDir");

                $zip = new ZipArchive();
                $zipPath = $tempDir . '/lead_files_' . uniqid() . '.zip';

                \Log::info("Creating ZIP at: $zipPath");

                $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

                if ($result !== TRUE) {
                    \Log::error("ZIP open failed: $result");
                    echo "Błąd tworzenia archiwum ZIP: " . $result;
                    return;
                }

                $addedFiles = 0;
                foreach ($lead->files as $index => $filePath) {
                    // Sprawdź różne możliwe ścieżki - uwzględnij strukturę z private
                    $possiblePaths = [
                        storage_path('app/private/' . $filePath), // Główna ścieżka - private storage
                        storage_path('app/' . $filePath),         // Bez private prefix
                        storage_path('app/public/' . $filePath),  // Public storage (mało prawdopodobne)
                        public_path('storage/' . $filePath),      // Symlink public/storage
                        $filePath // Pełna ścieżka (mało prawdopodobne)
                    ];

                    \Log::info("Looking for file: $filePath");

                    $fullPath = null;
                    foreach ($possiblePaths as $pathIndex => $path) {
                        \Log::info("  Checking path $pathIndex: $path - " . (file_exists($path) ? 'EXISTS' : 'NOT EXISTS'));
                        if (file_exists($path) && is_file($path)) {
                            $fullPath = $path;
                            \Log::info("  ✓ Found file at: $path");
                            break;
                        }
                    }

                    if ($fullPath && is_readable($fullPath)) {
                        $fileName = basename($filePath);
                        if (empty($fileName) || $fileName === '.' || $fileName === '..') {
                            $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
                            $fileName = "file-" . ($index + 1) . ($extension ? '.' . $extension : '');
                        }

                        // Unikaj duplikatów nazw
                        $originalFileName = $fileName;
                        $counter = 1;
                        while ($zip->locateName($fileName) !== false) {
                            $info = pathinfo($originalFileName);
                            $fileName = $info['filename'] . '_' . $counter . (isset($info['extension']) ? '.' . $info['extension'] : '');
                            $counter++;
                        }

                        if ($zip->addFile($fullPath, $fileName)) {
                            $addedFiles++;
                            \Log::info("✓ Added file: $fileName from $fullPath");
                        } else {
                            \Log::error("✗ Failed to add file: $fullPath");
                        }
                    } else {
                        \Log::warning("✗ File not found or not readable: $filePath");
                        \Log::warning("  Searched paths: " . implode(', ', $possiblePaths));

                        // Dodaj informację o brakującym pliku
                        $zip->addFromString("BRAKUJACY_PLIK_" . ($index + 1) . ".txt",
                            "Plik nie został znaleziony: " . $filePath . "\n" .
                            "Przeszukane ścieżki:\n" . implode("\n", $possiblePaths) . "\n" .
                            "Data: " . date('Y-m-d H:i:s'));
                    }
                }

                $closeResult = $zip->close();
                \Log::info("ZIP close result: " . ($closeResult ? 'SUCCESS' : 'FAILED'));

                if ($addedFiles === 0) {
                    echo "Brak dostępnych plików do archiwizacji";
                    if (file_exists($zipPath)) {
                        unlink($zipPath);
                    }
                    return;
                }

                // Sprawdź czy plik ZIP został utworzony i ma odpowiedni rozmiar
                if (!file_exists($zipPath)) {
                    \Log::error("ZIP file was not created: $zipPath");
                    echo "Błąd: Nie udało się utworzyć archiwum ZIP";
                    return;
                }

                $fileSize = filesize($zipPath);
                \Log::info("ZIP file created, size: $fileSize bytes");

                if ($fileSize === 0) {
                    \Log::error("ZIP file is empty");
                    echo "Błąd: Archiwum ZIP jest puste";
                    unlink($zipPath);
                    return;
                }

                // Wyślij plik
                $handle = fopen($zipPath, 'rb');
                if ($handle) {
                    while (!feof($handle)) {
                        echo fread($handle, 8192);
                        flush();
                    }
                    fclose($handle);
                    \Log::info("ZIP file sent successfully");
                } else {
                    \Log::error("Failed to open ZIP file for reading");
                }

                // Usuń tymczasowy plik
                if (file_exists($zipPath)) {
                    unlink($zipPath);
                    \Log::info("Temp ZIP file deleted");
                }

            }, $zipFileName, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } finally {
            // Przywróć poprzednie ustawienia error reporting
            error_reporting($originalErrorReporting);
        }
    }

    // Metoda do znalezienia najlepszego katalogu temp
    protected static function getBestTempDirectory(): string
    {
        $candidates = [
            storage_path('app/temp'),
            storage_path('app'),
            sys_get_temp_dir(),
            '/tmp'
        ];

        foreach ($candidates as $dir) {
            // Utwórz katalog jeśli nie istnieje
            if (!is_dir($dir)) {
                try {
                    mkdir($dir, 0755, true);
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Sprawdź czy możemy pisać
            if (is_dir($dir) && is_writable($dir)) {
                return $dir;
            }
        }

        // Ostateczny fallback
        return sys_get_temp_dir();
    }

    // Metoda fallback dla pojedynczego pliku
    protected static function downloadFirstFile(Lead $lead): \Illuminate\Http\Response
    {
        $filePath = $lead->files[0];

        $possiblePaths = [
            storage_path('app/private/' . $filePath), // Główna ścieżka - private storage
            storage_path('app/' . $filePath),         // Bez private prefix
            storage_path('app/public/' . $filePath),  // Public storage
            public_path('storage/' . $filePath),      // Symlink public/storage
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_file($path)) {
                return response()->download($path);
            }
        }

        return response('Plik nie został znaleziony', 404);
    }





}
