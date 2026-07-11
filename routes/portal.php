<?php

use App\Http\Controllers\Portal\LetterFileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:portal'])->group(function (): void {
    Route::get('/z/{k}/{date}/{file}', [LetterFileController::class, 'preview'])
        ->name('portal.letter-files.preview');

    Route::get('/file/{k}/{date}/{file}', [LetterFileController::class, 'download'])
        ->name('portal.letter-files.download');
});
