<?php

use App\Http\Controllers\BranchReportExportController;
use App\Http\Controllers\FilamentLayoutPreferenceController;
use App\Http\Controllers\FontgeneratorController;
use App\Http\Controllers\NeoznaczekController;
use App\Http\Controllers\OfferPdfDownloadController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\ReadfileController;
use Illuminate\Support\Facades\Route;

Route::get('fontgenerator', FontgeneratorController::class);
Route::get('neoznaczek', [NeoznaczekController::class, 'index']);
Route::get('neoznaczek/ocr', [NeoznaczekController::class, 'ocr']);

Route::get('/kancelaria/{path?}', function (?string $path = null) {
    $target = url('/'.ltrim((string) $path, '/'));

    if ($query = request()->getQueryString()) {
        $target .= '?'.$query;
    }

    return redirect()->to($target, 301);
})->where('path', '.*');

Route::post('/preferencje-uzytkownika/szerokosc-tabel', FilamentLayoutPreferenceController::class)
    ->middleware(['auth'])
    ->name('filament-layout.preferences.table-width');

Route::get('/oddzialy/{branch}/raport/export/{format}', BranchReportExportController::class)
    ->middleware(['auth'])
    ->whereIn('format', ['xlsx', 'pdf'])
    ->name('branches.report.export');

Route::middleware(['auth'])->group(function () {
    Route::get('/z/{k}/{data}/{file}', ReadfileController::class);
    Route::get('/file/{k}/{matter}/{file}', [ReadfileController::class, 'file']);
    Route::get('/neostamp/{date}/{label}', [ReadfileController::class, 'neostamp']);

    Route::get('/print/envelope/{records}', [PrintController::class, 'printEnvelope']);

    Route::get('/offers/{offer}/pdf', OfferPdfDownloadController::class)
        ->name('offers.pdf.download');
});
