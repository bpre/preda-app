<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MetaFetcher
{
    public function fetch(string $url): array
    {
        try {
            $resp = Http::withHeaders([
                    'User-Agent' => config('website.scraper.user_agent')
                ])
                ->timeout(config('website.scraper.timeout', 15))
                ->retry(2, 500)
                ->get(config('app.url') . $url);

            if (!$resp->ok()) {
                return $this->empty();
            }

            $html = $resp->body();

            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
            libxml_clear_errors();

            $xpath = new \DOMXPath($dom);

            $titleNode = $xpath->query('//title')->item(0);
            $title = $titleNode?->textContent;

            $descNode = $xpath->query('//meta[translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="description"]/@content')->item(0);
            $description = $descNode?->nodeValue;

            $h1Node = $xpath->query('//h1')->item(0);
            $h1 = $h1Node?->textContent;

            $h2Node = $xpath->query('//h2')->item(0);
            $h2 = $h2Node?->textContent;

            $norm = fn($v) => $v ? trim(preg_replace('/\s+/u', ' ', $v)) : null;
            $len  = fn($v) => $v !== null ? mb_strlen($v) : null;

            $title = $norm($title);
            $description = $norm($description);
            $h1 = $norm($h1);
            $h2 = $norm($h2);

            return [
                'title' => $title,
                'description' => $description,
                'h1' => $h1,
                'h2' => $h2,
                'title_length' => $len($title),
                'meta_description_length' => $len($description),
                'h1_length' => $len($h1),
                'h2_length' => $len($h2),
            ];
        } catch (\Throwable $e) {
            return $this->empty();
        }
    }

    private function empty(): array
    {
        return [
            'title' => null,
            'description' => null,
            'h1' => null,
            'h2' => null,
            'title_length' => null,
            'meta_description_length' => null,
            'h1_length' => null,
            'h2_length' => null,
        ];
    }
}
