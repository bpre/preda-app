<?php

namespace App\Support\Website;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PracticeContext
{
    public const CREDITS = 'credits';

    public const FAMILY_LAW = 'family-law';

    public const SESSION_KEY = 'website.practice_context';

    private const SHARED_PATHS = [
        '/kontakt',
        '/opinie',
        '/polityka-prywatnosci',
        '/mapa-strony',
    ];

    public static function current(?Request $request = null): string
    {
        $request ??= request();

        $context = self::contextForRequest($request);

        if ($context !== null && self::isActive($context)) {
            return $context;
        }

        if (! $request->hasSession()) {
            return self::CREDITS;
        }

        return self::normalize((string) $request->session()->get(self::SESSION_KEY, self::CREDITS));
    }

    public static function rememberForRequest(Request $request): void
    {
        if (! $request->isMethod('GET') || ! $request->hasSession()) {
            return;
        }

        $context = self::contextForRequest($request);

        if ($context === null || ! self::isActive($context)) {
            return;
        }

        $request->session()->put(self::SESSION_KEY, $context);
    }

    public static function abortIfInactiveForRequest(Request $request): void
    {
        $context = self::contextForRequest($request);

        if ($context !== null && ! self::isActive($context)) {
            abort(404);
        }
    }

    public static function contextForRequest(Request $request): ?string
    {
        $path = self::normalizedPath($request);

        if (Str::startsWith($path, ['/rozwod', '/podzial-majatku'])) {
            return self::FAMILY_LAW;
        }

        if (in_array($path, self::SHARED_PATHS, true)) {
            return null;
        }

        return self::CREDITS;
    }

    public static function activeContexts(): array
    {
        return array_values(array_unique([
            self::CREDITS,
            ...array_filter(self::configuredAdditionalContexts(), fn (string $context): bool => $context !== self::CREDITS),
        ]));
    }

    public static function isActive(string $context): bool
    {
        if ($context === self::CREDITS) {
            return true;
        }

        return in_array($context, self::configuredAdditionalContexts(), true);
    }

    private static function normalizedPath(Request $request): string
    {
        return '/' . trim($request->path(), '/');
    }

    private static function configuredAdditionalContexts(): array
    {
        $contexts = config('website.theme.active_contexts', []);

        if (is_string($contexts)) {
            $contexts = explode(',', $contexts);
        }

        if (! is_array($contexts)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $context): string => trim((string) $context),
            $contexts,
        )));
    }

    private static function normalize(string $context): string
    {
        return self::isActive($context) ? $context : self::CREDITS;
    }
}
