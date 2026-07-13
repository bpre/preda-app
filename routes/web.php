<?php

use App\Http\Controllers\MatterGeneratedDocumentDownloadController;
use Illuminate\Support\Facades\Route;

Route::domain(config('preda.domains.public'))
    ->group(base_path('routes/public.php'));

Route::domain(config('preda.domains.kancelaria'))
    ->group(base_path('routes/kancelaria.php'));

Route::domain(config('preda.domains.crm'))
    ->get('/umowy-do-analizy/{path?}', function (?string $path = null) {
        $target = '/leady'.($path ? '/'.$path : '');

        if ($query = request()->getQueryString()) {
            $target .= '?'.$query;
        }

        return redirect($target);
    })
    ->where('path', '.*')
    ->name('crm.analysis-leads.redirect');

Route::domain(config('preda.domains.crm'))
    ->get('/szanse/{path?}', function (?string $path = null) {
        $target = '/potencjalne'.($path ? '/'.$path : '');

        if ($query = request()->getQueryString()) {
            $target .= '?'.$query;
        }

        return redirect($target);
    })
    ->where('path', '.*')
    ->name('crm.chances.redirect');

Route::domain(config('preda.domains.portal'))
    ->group(base_path('routes/portal.php'));

Route::get('/wygenerowane-dokumenty/{document}/pobierz', MatterGeneratedDocumentDownloadController::class)
    ->middleware(['auth'])
    ->name('matter-generated-documents.download');

Route::get('/wygenerowane-dokumenty/{document}/podglad', [MatterGeneratedDocumentDownloadController::class, 'preview'])
    ->middleware(['auth'])
    ->name('matter-generated-documents.preview');
