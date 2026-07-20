<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\Website\SitemapGenerator;
use App\Models\Website\GoogleBusinessProfileConnection;
use App\Services\Integrations\GoogleAdsCampaignSyncService;
use App\Services\Integrations\GoogleBusinessProfileService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reviews:sync-google-business-profile {--publish=1}', function (GoogleBusinessProfileService $googleBusinessProfileService) {
    $connection = GoogleBusinessProfileConnection::query()->first();

    if (! $connection) {
        $this->error('Brak skonfigurowanego połączenia Google Business Profile.');

        return self::FAILURE;
    }

    try {
        $result = $googleBusinessProfileService->syncReviews(
            connection: $connection,
            publish: filter_var($this->option('publish'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true,
        );
    } catch (\Throwable $exception) {
        $this->error($exception->getMessage());

        return self::FAILURE;
    }

    $this->info("Dodano: {$result['created']}, zaktualizowano: {$result['updated']}, pominięto: {$result['skipped']}.");

    return self::SUCCESS;
})->purpose('Synchronizuje opinie z Google Business Profile do lokalnej bazy danych.');

Artisan::command('google-ads:sync-campaigns {--monthly-months=36}', function (GoogleAdsCampaignSyncService $googleAdsCampaignSyncService) {
    try {
        $result = $googleAdsCampaignSyncService->syncCampaigns(
            monthlyMonths: max(0, (int) $this->option('monthly-months')),
        );
    } catch (\Throwable $exception) {
        $this->error($exception->getMessage());

        return self::FAILURE;
    }

    $monthly = $result['monthly_metrics'] ?? [];

    $this->info("Kampanie - dodano: {$result['created']}, zaktualizowano: {$result['updated']}, pominięto: {$result['skipped']}.");
    $this->info('Metryki miesięczne - dodano: '.($monthly['created'] ?? 0).', zaktualizowano: '.($monthly['updated'] ?? 0).', pominięto: '.($monthly['skipped'] ?? 0).'.');
    $this->info("Powiązano leady: {$result['linked_leads']}.");

    return self::SUCCESS;
})->purpose('Synchronizuje kampanie Google Ads do lokalnej bazy CRM.');

Schedule::command('google-ads:sync-campaigns')
    ->dailyAt('06:15')
    ->when(fn (): bool => app(GoogleAdsCampaignSyncService::class)->isConfigured());

Artisan::command('sitemap:generate', function (SitemapGenerator $sitemapGenerator) {
    $path = $sitemapGenerator->generate();

    $this->info("Sitemap została wygenerowana: {$path}");

    return self::SUCCESS;
})->purpose('Generuje sitemapę XML do katalogu public.');
