<?php

use App\Http\Controllers\CrmLeadStatsExportController;
use App\Http\Controllers\CrmPotentialMatterLeadFileDownloadController;
use App\Http\Controllers\MailgunWebhookController;
use App\Http\Controllers\MatterGeneratedDocumentDownloadController;
use App\Http\Controllers\UserImpersonationController;
use App\Http\Middleware\IsActiveUser;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/mailgun/events', MailgunWebhookController::class)
    ->name('webhooks.mailgun.events');

Route::domain(config('preda.domains.public'))
    ->group(base_path('routes/public.php'));

Route::domain(config('preda.domains.kancelaria'))
    ->group(base_path('routes/kancelaria.php'));

Route::domain(config('preda.domains.crm'))
    ->middleware(['auth', IsActiveUser::class])
    ->get('/statystyki-leadow/export', CrmLeadStatsExportController::class)
    ->name('crm.lead-stats.export');

Route::domain(config('preda.domains.crm'))
    ->middleware(['auth', IsActiveUser::class])
    ->get('/potencjalne/{matter}/pliki-leada/{fileIndex}', CrmPotentialMatterLeadFileDownloadController::class)
    ->whereNumber('fileIndex')
    ->name('crm.potential-matter.lead-file.download');

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

foreach (['kancelaria', 'crm', 'cms'] as $panelId) {
    Route::domain(config("preda.domains.{$panelId}"))
        ->middleware(['auth', IsActiveUser::class])
        ->get('/impersonacja/rozpocznij/{user}', [UserImpersonationController::class, 'start'])
        ->defaults('preda_panel_id', $panelId)
        ->name("impersonation.start.{$panelId}");

    Route::domain(config("preda.domains.{$panelId}"))
        ->get('/impersonacja/przejmij/{token}', [UserImpersonationController::class, 'consume'])
        ->whereAlphaNumeric('token')
        ->defaults('preda_panel_id', $panelId)
        ->name("impersonation.consume.{$panelId}");

    Route::domain(config("preda.domains.{$panelId}"))
        ->middleware(['auth', IsActiveUser::class])
        ->post('/impersonacja/zakoncz', [UserImpersonationController::class, 'stop'])
        ->defaults('preda_panel_id', $panelId)
        ->name("impersonation.stop.{$panelId}");
}

Route::get('/wygenerowane-dokumenty/{document}/pobierz', MatterGeneratedDocumentDownloadController::class)
    ->middleware(['auth'])
    ->name('matter-generated-documents.download');

Route::get('/wygenerowane-dokumenty/{document}/podglad', [MatterGeneratedDocumentDownloadController::class, 'preview'])
    ->middleware(['auth'])
    ->name('matter-generated-documents.preview');
