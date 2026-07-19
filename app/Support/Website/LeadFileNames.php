<?php

namespace App\Support\Website;

use App\Models\Website\Lead;

class LeadFileNames
{
    public static function displayName(?Lead $lead, string $path, int $index): string
    {
        $name = self::storedName($lead, $path) ?? basename($path);

        if (blank($name) || self::looksLikeGeneratedName($name)) {
            return 'Dokument '.($index + 1);
        }

        return self::sanitize($name) ?: 'Dokument '.($index + 1);
    }

    public static function downloadName(?Lead $lead, string $path, int $index): string
    {
        $name = self::storedName($lead, $path) ?? basename($path);

        return self::sanitize($name) ?: 'plik-'.($index + 1);
    }

    public static function stageName(?Lead $lead, string $path, int $index): string
    {
        return self::downloadName($lead, $path, $index);
    }

    /**
     * @param  array<int, string>  $files
     * @param  mixed  $fileNames
     * @return array<string, string>
     */
    public static function mapForFiles(array $files, mixed $fileNames): array
    {
        if (! is_array($fileNames)) {
            return [];
        }

        $map = [];

        foreach ($files as $path) {
            if (! is_string($path) || blank($path)) {
                continue;
            }

            $name = self::sanitize((string) ($fileNames[$path] ?? ''));

            if (filled($name)) {
                $map[$path] = $name;
            }
        }

        return $map;
    }

    private static function storedName(?Lead $lead, string $path): ?string
    {
        $fileNames = $lead?->files_names;

        if (! is_array($fileNames)) {
            return null;
        }

        $name = $fileNames[$path] ?? null;

        return filled($name) ? (string) $name : null;
    }

    private static function sanitize(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        $name = basename($name);
        $name = preg_replace('/[\x00-\x1F\x7F]+/u', '', $name) ?? '';

        return trim($name);
    }

    private static function looksLikeGeneratedName(string $name): bool
    {
        return (bool) preg_match('/^[a-z0-9]{20,}\.[a-z0-9]+$/i', $name);
    }
}
