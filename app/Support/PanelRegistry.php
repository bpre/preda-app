<?php

namespace App\Support;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;

class PanelRegistry
{
    public static function definitions(): array
    {
        return [
            'kancelaria' => [
                'label' => 'Kancelaria',
                'domain' => config('preda.domains.kancelaria'),
            ],
            'crm' => [
                'label' => 'CRM',
                'domain' => config('preda.domains.crm'),
            ],
            'cms' => [
                'label' => 'Strona www',
                'domain' => config('preda.domains.cms'),
            ],
        ];
    }

    public static function availableFor(?Authenticatable $user): array
    {
        if (! $user instanceof User) {
            return [];
        }

        return collect(self::definitions())
            ->filter(fn (array $definition, string $panelId): bool => $user->canAccessPredaPanel($panelId))
            ->map(fn (array $definition, string $panelId): array => [
                ...$definition,
                'id' => $panelId,
                'url' => self::urlFor($panelId),
                'active' => Filament::getCurrentPanel()?->getId() === $panelId,
            ])
            ->values()
            ->all();
    }

    public static function urlFor(string $panelId): string
    {
        $domain = config("preda.domains.{$panelId}");
        $scheme = config('preda.scheme', 'http');
        $port = config('preda.local_port');

        if (blank($domain)) {
            return '#';
        }

        $host = (string) $domain;

        if (app()->environment('local') && filled($port)) {
            $host .= ':'.$port;
        }

        return "{$scheme}://{$host}";
    }
}
