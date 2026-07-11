<?php

namespace App\Filament\Website\Resources\PageSnapshots\Pages;

use Filament\Actions\Action;
use App\Services\MetaFetcher;
use App\Services\SiteCrawler;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\DB;
use App\Models\Website\PageSnapshot;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Website\Resources\PageSnapshots\PageSnapshotResource;


class ListPageSnapshots extends ListRecords
{
    protected static string $resource = PageSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
            Action::make('run')
                ->label('Pobierz meta i nagłówki')
                ->requiresConfirmation()
                ->action(fn() => $this->runScrape())
                ->color('primary')
                ->icon('heroicon-o-arrow-path'),

            Action::make('spider')
                ->label('Sprawdź podstrony')
                ->icon('heroicon-o-globe-alt')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Uruchomić pajączka?')
                ->modalSubheading('Przejdzie od ' . config('app.url') . ' i sprawdzi, których podstron brakuje w page_snapshots.')
                ->action(fn () => $this->runSpider()),
        ];
    }

    protected function runScrape(): void
    {

        $dynamic_urls = collect(config('website.pages.sets'))
            ->flatMap(function ($set) {
                $query = $set['model']::query();

                if ($set['published_only']) {
                    $query->where('is_published', 1);
                }

                if (isset($set['category'])) {
                    $query->where('category', $set['category']);
                }

                return $query->pluck('slug')->map(fn($slug) => "/{$set['prefix']}{$slug}");
            })
            ->all();

        $urls = array_merge(config('website.pages.urls', []), $dynamic_urls);

        if (empty($urls)) {
            \Filament\Notifications\Notification::make()->title('Brak URL-i w config/scrape.php')->danger()->send();
            return;
        }

        $fetcher = app(\App\Services\MetaFetcher::class);

        \Illuminate\Support\Facades\DB::transaction(function () use ($urls, $fetcher) {
            // 1) czyścimy wszystko
            PageSnapshot::query()->delete();

            // 2) wstawiamy nowe
            foreach ($urls as $url) {
                $data = $fetcher->fetch($url);

                PageSnapshot::create([
                    'url' => $url,
                    'category' => $this->extractCategory($url),

                    'title' => $data['title'],
                    'meta_description' => $data['description'],
                    'h1' => $data['h1'],
                    'h2' => $data['h2'],

                    'title_length' => $data['title_length'],
                    'meta_description_length' => $data['meta_description_length'],
                    'h1_length' => $data['h1_length'],
                    'h2_length' => $data['h2_length'],

                    'fetched_at' => now(),
                ]);
            }

            // 3) unikalność title i h1 (per rekord)
            $rows = PageSnapshot::select('id','title','h1')->get();

            $titleCounts = $rows
                ->filter(fn($r) => ($r->title ?? '') !== '')
                ->groupBy('title')
                ->map->count();

            $h1Counts = $rows
                ->filter(fn($r) => ($r->h1 ?? '') !== '')
                ->groupBy('h1')
                ->map->count();

            foreach ($rows as $r) {
                $isTitleUnique = $r->title && ($titleCounts[$r->title] ?? 0) === 1;
                $isH1Unique    = $r->h1 && ($h1Counts[$r->h1] ?? 0) === 1;

                // jeśli brak wartości — ustawiamy null, nie false
                PageSnapshot::whereKey($r->id)->update([
                    'is_title_unique' => $r->title ? $isTitleUnique : null,
                    'is_h1_unique'    => $r->h1 ? $isH1Unique : null,
                ]);
            }
        });

        \Filament\Notifications\Notification::make()
            ->title('Zrobione')
            ->body('Dane pobrane. Długości i unikalność title/H1 wyliczone.')
            ->success()
            ->send();
    }

    private function extractCategory(string $url): ?string
    {
        $parts = parse_url($url);
        $path = $parts['path'] ?? '';
        // rozbij ścieżkę i usuń puste elementy (podwójne slashe / trailing slash)
        $segments = array_values(array_filter(explode('/', $path), fn ($s) => $s !== ''));

        // warunek „>=4 slashe w URL” ≈ „co najmniej 2 segmenty ścieżki”
        if (count($segments) >= 2) {
            return $segments[0]; // fragment między 3. i 4. slashem
        }
        return null;
    }

    protected function runSpider(): void
    {
        $startUrl   = config('app.url');
        $startOrigin = app(SiteCrawler::class)::origin($startUrl);
        $crawler    = app(SiteCrawler::class);

        $found = $crawler->crawl(
            $startUrl,
            maxPages: (int) config('website.scraper.crawl_max_pages', 500),
            timeout:  (int) config('website.scraper.timeout', 15),
        );

        // NORMALIZACJA DO ŚCIEŻEK
        $existingPaths = PageSnapshot::query()
            ->pluck('url')
            ->map(fn ($u) => SiteCrawler::normalizePath((string) $u))
            ->filter()
            ->unique()
            ->values();

        $foundPaths = collect($found)
            ->map(fn ($u) => SiteCrawler::normalizePath((string) $u))
            ->filter()
            ->unique()
            ->values();

        $missingPaths = $foundPaths->diff($existingPaths)->values()->all();

        if (empty($missingPaths)) {
            \Notification::make()
                ->title('Wszystkie podstrony zostały uwzględnione')
                ->success()
                ->send();
            return;
        }

        // Zbuduj pełne URL-e do prezentacji (na bazie origin)
        $missingUrls = array_map(
            fn ($p) => rtrim($startOrigin, '/') . ($p === '/' ? '/' : $p),
            $missingPaths
        );

        $list = implode("\n", array_slice($missingUrls, 0, 50));
        $more = count($missingUrls) > 50 ? "\n… i " . (count($missingUrls) - 50) . " więcej" : '';

        Notification::make()
            ->title('Brakuje podstron (' . count($missingUrls) . ')')
            ->body($list . $more)
            ->warning()
            ->persistent()
            ->send();
    }

}
