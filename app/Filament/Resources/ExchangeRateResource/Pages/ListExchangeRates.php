<?php

namespace App\Filament\Resources\ExchangeRateResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use App\Filament\Resources\ExchangeRateResource;
use App\Models\ExchangeRate;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;
use Throwable;

class ListExchangeRates extends ListRecords
{
    protected static string $resource = ExchangeRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('importXls')
                ->label('Importuj XLS')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Import kursów walut z XLS')
                ->schema([
                    FileUpload::make('files')
                        ->label('Pliki XLS/XLSX')
                        ->required()
                        ->multiple()
                        ->disk('local')
                        ->directory('imports/exchange-rates')
                        ->preserveFilenames()
                        ->acceptedFileTypes([
                            'application/vnd.ms-excel', // xls
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
                        ]),
                ])
                ->action(function (array $data): void {
                    $paths = $data['files'] ?? [];

                    if (is_string($paths)) {
                        $paths = [$paths];
                    }

                    $totalImported = 0;
                    $failed = [];

                    $importOne = function (string $relativePath) use (&$totalImported, &$failed): void {
                        try {
                            $fullPath = Storage::disk('local')->path($relativePath);

                            $spreadsheet = IOFactory::load($fullPath);
                            $sheet = $spreadsheet->getActiveSheet();

                            $rows = $sheet->toArray(null, true, true, true);
                            if (count($rows) < 2) {
                                return;
                            }

                            $header = array_shift($rows);

                            // ---------- WYKRYWANIE KOLUMN: DATA + USD/EUR/CHF ----------

                            $normalize = static function ($value): string {
                                $s = trim((string) $value);
                                $s = str_replace(["\u{00A0}", "\t"], ' ', $s); // NBSP + tab na spację
                                $s = preg_replace('/\s+/', ' ', $s) ?? $s;     // wielokrotne spacje
                                return mb_strtolower($s);
                            };

                            $findCol = function (callable $predicate) use ($header, $normalize): ?string {
                                foreach ($header as $col => $name) {
                                    $n = $normalize($name);
                                    if ($n !== '' && $predicate($n)) {
                                        return $col;
                                    }
                                }
                                return null;
                            };

                            // DATA: nagłówek zawiera "data"; fallback: dokładnie "kurs średni"
                            $dateCol = $findCol(fn (string $h) => str_contains($h, 'data'))
                                ?? $findCol(fn (string $h) => $h === 'kurs średni');

                            // WALUTY: wystarczy, że nagłówek zawiera skrót
                            $usdCol = $findCol(fn (string $h) => str_contains($h, 'usd'));
                            $eurCol = $findCol(fn (string $h) => str_contains($h, 'eur'));
                            $chfCol = $findCol(fn (string $h) => str_contains($h, 'chf'));

                            if (! $dateCol || ! $usdCol || ! $eurCol || ! $chfCol) {
                                throw new RuntimeException(
                                    'Brak wymaganych kolumn. Wymagane: data (nagłówek zawiera "data" lub "KURS ŚREDNI") oraz USD/EUR/CHF (nagłówki zawierają te skróty).'
                                );
                            }

                            // ---------- PARSOWANIE WARTOŚCI ----------

                            $toDecimal = static function ($value): ?string {
                                if ($value === null || $value === '') return null;
                                if (is_numeric($value)) return (string) $value;

                                $value = str_replace([' ', "\u{00A0}"], '', (string) $value);
                                $value = str_replace(',', '.', $value);

                                return is_numeric($value) ? $value : null;
                            };

                            $importedFromThisFile = 0;

                            DB::transaction(function () use (
                                $rows, $dateCol, $usdCol, $eurCol, $chfCol, $toDecimal, &$importedFromThisFile
                            ) {
                                foreach ($rows as $row) {
                                    $rawDate = $row[$dateCol] ?? null;
                                    if ($rawDate === null || $rawDate === '') {
                                        continue;
                                    }

                                    $date = is_numeric($rawDate)
                                        ? Carbon::instance(ExcelDate::excelToDateTimeObject($rawDate))->toDateString()
                                        : Carbon::parse((string) $rawDate)->toDateString();

                                    $usd = $toDecimal($row[$usdCol] ?? null);
                                    $eur = $toDecimal($row[$eurCol] ?? null);
                                    $chf = $toDecimal($row[$chfCol] ?? null);

                                    if ($usd === null && $eur === null && $chf === null) {
                                        continue;
                                    }

                                    ExchangeRate::updateOrCreate(
                                        ['date' => $date],
                                        ['usd' => $usd, 'eur' => $eur, 'chf' => $chf],
                                    );

                                    $importedFromThisFile++;
                                }
                            });

                            $totalImported += $importedFromThisFile;
                        } catch (Throwable $e) {
                            $failed[] = basename($relativePath) . ': ' . $e->getMessage();
                        }
                    };

                    foreach ($paths as $relativePath) {
                        $importOne($relativePath);
                    }

                    $note = "Zaimportowano / zaktualizowano łącznie: {$totalImported} rekordów.";

                    if (! empty($failed)) {
                        $note .= "\n\nBłędy:\n- " . implode("\n- ", $failed);

                        Notification::make()
                            ->warning()
                            ->title('Import zakończony częściowo')
                            ->body($note)
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title('Import zakończony')
                        ->body($note)
                        ->send();
                }),
        ];
    }
}
