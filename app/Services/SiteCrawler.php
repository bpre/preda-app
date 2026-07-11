<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SiteCrawler
{
    public function crawl(string $startUrl, int $maxPages = 500, int $timeout = 10): array
    {
        $baseOrigin = self::origin($startUrl);
        $baseHost   = parse_url($baseOrigin, PHP_URL_HOST);

        $queue = [self::normalizeUrl($startUrl)];
        $seen = [];
        $visited = [];

        while (!empty($queue) && count($visited) < $maxPages) {
            $url = array_shift($queue);
            if (!$url || isset($seen[$url])) continue;

            $seen[$url] = true;
            $visited[]  = $url;

            try {
                $resp = Http::withHeaders([
                        'User-Agent' => config('website.scraper.user_agent', 'LaravelMetaBot/1.0')
                    ])
                    ->timeout($timeout)
                    ->retry(1, 200)
                    ->get($url);
            } catch (\Throwable $e) {
                continue;
            }

            if (!$resp->ok()) continue;

            $ct = strtolower($resp->header('Content-Type', ''));
            if (strpos($ct, 'text/html') === false) continue;

            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML($resp->body());
            libxml_clear_errors();
            $xpath = new \DOMXPath($dom);

            foreach ($xpath->query('//a[@href]/@href') as $attr) {
                $href = trim($attr->nodeValue ?? '');
                $abs  = $this->resolve($href, $url, $baseOrigin);
                if (!$abs) continue;

                $abs = self::normalizeUrl($abs);
                if (!$abs) continue;

                // trzymaj się hosta startowego (www == bez www)
                $host     = parse_url($abs, PHP_URL_HOST) ?? '';
                $normHost = self::stripWww(strtolower($host));
                $normBase = self::stripWww(strtolower($baseHost ?? ''));

                if ($normHost !== $normBase) continue;

                if (!isset($seen[$abs])) {
                    $queue[] = $abs;
                }
            }
        }

        return array_values(array_unique($visited));
    }

    public static function normalizeUrl(string $url): ?string
    {
        $p = parse_url($url);
        if (!isset($p['scheme'], $p['host'])) return null;

        $scheme = strtolower($p['scheme']);
        $host   = strtolower($p['host']);
        $port   = isset($p['port']) ? ':' . $p['port'] : '';
        // usuń port domyślny
        if (($scheme === 'http'  && $port === ':80') ||
            ($scheme === 'https' && $port === ':443')) {
            $port = '';
        }

        // ścieżka
        $path = $p['path'] ?? '/';
        if ($path === '') $path = '/';
        // sklej wielokrotne slashe, odkoduj % i utnij trailing slash (poza "/")
        $path = preg_replace('#/+#', '/', $path);
        $path = rawurldecode($path);
        if ($path !== '/') $path = rtrim($path, '/');

        // znormalizuj index.* na katalog
        $base = strtolower(basename($path));
        if (in_array($base, ['index', 'index.php', 'index.html'], true)) {
            $dir = rtrim(dirname($path), '/');
            $path = $dir === '' ? '/' : $dir;
        }

        return "{$scheme}://{$host}{$port}{$path}";
    }

    /** Zwraca tylko znormalizowaną ścieżkę (do porównań). */
    public static function normalizePath(string $url): ?string
    {
        $p = parse_url($url);
        $path = $p['path'] ?? '/';
        if ($path === '') $path = '/';
        $path = preg_replace('#/+#', '/', $path);
        $path = rawurldecode($path);
        if ($path !== '/') $path = rtrim($path, '/');

        $base = strtolower(basename($path));
        if (in_array($base, ['index', 'index.php', 'index.html'], true)) {
            $dir = rtrim(dirname($path), '/');
            $path = $dir === '' ? '/' : $dir;
        }
        return $path;
    }

    private static function stripWww(string $host): string
    {
        return str_starts_with($host, 'www.') ? substr($host, 4) : $host;
    }

    public static function origin(string $url): string
    {
        $p = parse_url($url);
        $scheme = strtolower($p['scheme'] ?? 'http');
        $host   = strtolower($p['host'] ?? '');
        $port   = isset($p['port']) ? ':' . $p['port'] : '';
        if (($scheme === 'http'  && $port === ':80') ||
            ($scheme === 'https' && $port === ':443')) {
            $port = '';
        }
        return "{$scheme}://{$host}{$port}";
    }

    private function resolve(string $href, string $currentUrl, string $origin): ?string
    {
        if ($href === '' || str_starts_with($href, '#')
            || str_starts_with($href, 'mailto:')
            || str_starts_with($href, 'tel:')
            || str_starts_with($href, 'javascript:')
        ) {
            return null;
        }

        if (preg_match('#^https?://#i', $href)) {
            return $href;
        }

        if (str_starts_with($href, '//')) {
            $scheme = parse_url($origin, PHP_URL_SCHEME) ?: 'http';
            return $scheme . ':' . $href;
        }

        if (str_starts_with($href, '/')) {
            return rtrim($origin, '/') . $href;
        }

        // relative do katalogu bieżącej strony
        $p = parse_url($currentUrl);
        $basePath = $p['path'] ?? '/';
        $dir = (str_ends_with($basePath, '/')) ? $basePath : (rtrim(dirname($basePath), '/') . '/');
        return rtrim($origin, '/') . '/' . ltrim($dir . $href, '/');
    }
}
