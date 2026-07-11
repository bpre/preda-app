<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\Website\SitemapGenerator;
use App\Models\Website\GoogleBusinessProfileConnection;
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

Artisan::command('sitemap:generate', function (SitemapGenerator $sitemapGenerator) {
    $path = $sitemapGenerator->generate();

    $this->info("Sitemap została wygenerowana: {$path}");

    return self::SUCCESS;
})->purpose('Generuje sitemapę XML do katalogu public.');
