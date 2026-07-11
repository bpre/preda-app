<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PanelAccess
{
    public static function panelIds(): array
    {
        return array_keys(PanelRegistry::definitions());
    }

    public static function options(): array
    {
        return collect(PanelRegistry::definitions())
            ->mapWithKeys(fn (array $definition, string $panelId): array => [
                $panelId => $definition['label'],
            ])
            ->all();
    }

    public static function permissionName(string $panelId): string
    {
        return "access_{$panelId}_panel";
    }

    public static function permissionNames(?array $panelIds = null): array
    {
        return array_map(
            fn (string $panelId): string => self::permissionName($panelId),
            self::normalizePanelIds($panelIds ?? self::panelIds()),
        );
    }

    public static function normalizePanelIds(array $panelIds): array
    {
        $validPanelIds = self::panelIds();

        return collect($panelIds)
            ->map(fn (mixed $panelId): string => (string) $panelId)
            ->filter(fn (string $panelId): bool => in_array($panelId, $validPanelIds, true))
            ->unique()
            ->values()
            ->all();
    }

    public static function directPanelsFor(User $user): array
    {
        $directPermissionNames = $user->getDirectPermissions()
            ->pluck('name')
            ->all();

        return collect(self::panelIds())
            ->filter(fn (string $panelId): bool => in_array(self::permissionName($panelId), $directPermissionNames, true))
            ->values()
            ->all();
    }

    public static function ensurePermissions(?array $panelIds = null): void
    {
        $created = false;

        foreach (self::permissionNames($panelIds) as $permissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

            $created = $created || $permission->wasRecentlyCreated;
        }

        if ($created) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public static function grantDirect(User $user, array $panelIds): void
    {
        $permissionNames = self::permissionNames($panelIds);

        if ($permissionNames === []) {
            return;
        }

        self::ensurePermissions($panelIds);

        $user->givePermissionTo($permissionNames);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public static function revokeDirect(User $user, array $panelIds): void
    {
        $permissionNames = self::permissionNames($panelIds);

        if ($permissionNames === []) {
            return;
        }

        self::ensurePermissions($panelIds);

        $user->revokePermissionTo($permissionNames);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public static function syncDirect(User $user, array $panelIds): void
    {
        $selectedPanelIds = self::normalizePanelIds($panelIds);

        self::ensurePermissions();

        foreach (self::panelIds() as $panelId) {
            $permissionName = self::permissionName($panelId);

            if (in_array($panelId, $selectedPanelIds, true)) {
                if (! $user->hasDirectPermission($permissionName)) {
                    $user->givePermissionTo($permissionName);
                }

                continue;
            }

            if ($user->hasDirectPermission($permissionName)) {
                $user->revokePermissionTo($permissionName);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
