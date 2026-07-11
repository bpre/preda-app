<?php

namespace App\Support;

use BezhanSalleh\FilamentShield\FilamentShield;
use Filament\Facades\Filament;
use Filament\Panel;

class ShieldPanelPermissions
{
    public static function groups(): array
    {
        $seenPermissionKeys = [];

        return collect(PanelRegistry::definitions())
            ->mapWithKeys(function (array $definition, string $panelId) use (&$seenPermissionKeys): array {
                $entities = self::entitiesForPanel($panelId);

                $resources = self::resourcesWithUniquePermissions(
                    $entities['resources'],
                    $seenPermissionKeys,
                );

                $pages = self::uniquePermissionOptions(
                    self::flatPermissionOptions($entities['pages']),
                    $seenPermissionKeys,
                );

                $widgets = self::uniquePermissionOptions(
                    self::flatPermissionOptions($entities['widgets']),
                    $seenPermissionKeys,
                );

                return [
                    $panelId => [
                        'id' => $panelId,
                        'label' => $definition['label'],
                        'resources' => $resources,
                        'pages' => $pages,
                        'widgets' => $widgets,
                        'count' => collect($resources)->sum(fn (array $resource): int => count($resource['permissionOptions']))
                            + count($pages)
                            + count($widgets),
                    ],
                ];
            })
            ->filter(fn (array $group): bool => $group['count'] > 0)
            ->all();
    }

    private static function entitiesForPanel(string $panelId): array
    {
        $currentPanel = Filament::getCurrentPanel();

        Filament::setCurrentPanel($panelId);

        try {
            $shield = (new FilamentShield)
                ->buildPermissionKeyUsing(fn (string $entity, string $affix, string $subject): string => ShieldPermissionKeys::build(
                    entity: $entity,
                    affix: $affix,
                    subject: $subject,
                ));

            return [
                'resources' => $shield->transformResources() ?? [],
                'pages' => $shield->transformPages() ?? [],
                'widgets' => $shield->transformWidgets() ?? [],
            ];
        } finally {
            Filament::setCurrentPanel($currentPanel instanceof Panel ? $currentPanel : null);
        }
    }

    private static function resourcesWithUniquePermissions(array $resources, array &$seenPermissionKeys): array
    {
        return collect($resources)
            ->map(function (array $resource) use (&$seenPermissionKeys): array {
                $options = self::uniquePermissionOptions(
                    collect($resource['permissions'])
                        ->mapWithKeys(fn (array $permission): array => [
                            $permission['key'] => $permission['label'],
                        ])
                        ->all(),
                    $seenPermissionKeys,
                );

                return [
                    ...$resource,
                    'label' => self::resourceLabel($resource),
                    'permissionOptions' => $options,
                ];
            })
            ->filter(fn (array $resource): bool => $resource['permissionOptions'] !== [])
            ->values()
            ->all();
    }

    private static function flatPermissionOptions(array $entities): array
    {
        return collect($entities)
            ->flatMap(fn (array $entity): array => collect($entity['permissions'])
                ->mapWithKeys(fn (string $label, string $permission): array => [
                    $permission => $label,
                ])
                ->all())
            ->all();
    }

    private static function uniquePermissionOptions(array $options, array &$seenPermissionKeys): array
    {
        return collect($options)
            ->filter(function (string $label, string $permission) use (&$seenPermissionKeys): bool {
                if (isset($seenPermissionKeys[$permission])) {
                    return false;
                }

                $seenPermissionKeys[$permission] = true;

                return true;
            })
            ->all();
    }

    private static function resourceLabel(array $resource): string
    {
        $resourceClass = $resource['resourceFqcn'];

        if (method_exists($resourceClass, 'getPluralModelLabel')) {
            return $resourceClass::getPluralModelLabel();
        }

        return $resource['model'];
    }
}
