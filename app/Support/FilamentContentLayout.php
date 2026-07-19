<?php

namespace App\Support;

use App\Models\User;
use Filament\Pages\Page as FilamentPage;
use Filament\Resources\Pages\Concerns\HasRelationManagers;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\Width;
use Illuminate\Support\Arr;
use Throwable;

class FilamentContentLayout
{
    public const PREFERENCE_KEYS = [
        'content_full_width',
        'content_max_width',
        'content_alignment',
        'record_list_pages_full_width',
        'record_list_pages_full_width_toggle',
        'record_list_pages_full_width_storage_key',
    ];

    protected const BOOLEAN_PREFERENCE_KEYS = [
        'content_full_width',
        'record_list_pages_full_width',
        'record_list_pages_full_width_toggle',
    ];

    protected const CONTENT_MAX_WIDTH_OPTIONS = [
        'lg',
        'xl',
        '2xl',
        '3xl',
        '4xl',
        '5xl',
        '6xl',
        '7xl',
    ];

    protected const WIDTH_CSS_VALUES = [
        '3xs' => 'var(--container-3xs)',
        '2xs' => 'var(--container-2xs)',
        'xs' => 'var(--container-xs)',
        'sm' => 'var(--container-sm)',
        'md' => 'var(--container-md)',
        'lg' => 'var(--container-lg)',
        'xl' => 'var(--container-xl)',
        '2xl' => 'var(--container-2xl)',
        '3xl' => 'var(--container-3xl)',
        '4xl' => 'var(--container-4xl)',
        '5xl' => 'var(--container-5xl)',
        '6xl' => 'var(--container-6xl)',
        '7xl' => 'var(--container-7xl)',
        'full' => '100%',
        'screen' => '100vw',
    ];

    public static function defaultPreferences(): array
    {
        return self::sanitizePreferences([
            'content_full_width' => config('filament-layout.content_full_width', false),
            'content_max_width' => config('filament-layout.content_max_width', '7xl'),
            'content_alignment' => config('filament-layout.content_alignment', 'left'),
            'record_list_pages_full_width' => config('filament-layout.record_list_pages_full_width', true),
            'record_list_pages_full_width_toggle' => config('filament-layout.record_list_pages_full_width_toggle', true),
            'record_list_pages_full_width_storage_key' => config(
                'filament-layout.record_list_pages_full_width_storage_key',
                'filament-record-list-pages-full-width',
            ),
        ]);
    }

    public static function preferences(?User $user = null): array
    {
        $user ??= self::currentUser();
        $storedPreferences = $user?->filament_layout_preferences;

        if (! is_array($storedPreferences)) {
            $storedPreferences = [];
        }

        return self::sanitizePreferences(array_replace(
            self::defaultPreferences(),
            $storedPreferences,
        ));
    }

    public static function savePreferences(User $user, array $preferences): array
    {
        $preferences = self::sanitizePreferences(array_replace(
            self::defaultPreferences(),
            Arr::only($preferences, self::PREFERENCE_KEYS),
        ));

        $user->forceFill([
            'filament_layout_preferences' => $preferences,
        ])->save();

        return $preferences;
    }

    public static function saveRecordListPagesFullWidth(User $user, bool $fullWidth): array
    {
        return self::savePreferences($user, [
            ...self::preferences($user),
            'record_list_pages_full_width' => $fullWidth,
        ]);
    }

    public static function contentMaxWidthOptions(): array
    {
        return collect(self::CONTENT_MAX_WIDTH_OPTIONS)
            ->mapWithKeys(fn (string $width): array => [$width => $width])
            ->all();
    }

    public static function defaultMaxContentWidth(): Width | string | null
    {
        if (self::shouldUseFullWidthForRecordListPagesByDefault()) {
            return self::contentMaxWidth();
        }

        if (self::shouldUseFullWidthForAllContent()) {
            return Width::Full;
        }

        return self::contentMaxWidth();
    }

    public static function shouldUseFullWidthForAllContent(): bool
    {
        return (bool) self::preferences()['content_full_width'];
    }

    public static function contentMaxWidth(): string
    {
        return (string) self::preferences()['content_max_width'];
    }

    public static function shouldAlignWrappedContentToLeft(): bool
    {
        return self::preferences()['content_alignment'] === 'left';
    }

    public static function shouldUseFullWidthForCurrentRecordListPage(): bool
    {
        return self::shouldUseFullWidthForCurrentTablePage();
    }

    public static function shouldUseFullWidthForCurrentTablePage(): bool
    {
        return self::isCurrentTablePage() && self::shouldUseFullWidthForRecordListPagesByDefault();
    }

    public static function isCurrentTablePage(): bool
    {
        $pageClass = self::currentPageClass();

        if (! $pageClass) {
            return false;
        }

        if (is_a($pageClass, ListRecords::class, true)) {
            return true;
        }

        if (is_a($pageClass, ManageRelatedRecords::class, true)) {
            return true;
        }

        return self::resourcePageHasActiveRelationManager($pageClass);
    }

    public static function shouldUseFullWidthForRecordListPagesByDefault(): bool
    {
        return (bool) self::preferences()['record_list_pages_full_width'];
    }

    public static function shouldShowRecordListPagesFullWidthToggle(): bool
    {
        return (bool) self::preferences()['record_list_pages_full_width_toggle'];
    }

    public static function recordListPagesFullWidthStorageKey(): string
    {
        return (string) self::preferences()['record_list_pages_full_width_storage_key'];
    }

    public static function defaultRecordListPagesWidthMode(): string
    {
        return self::shouldUseFullWidthForRecordListPagesByDefault() ? 'full' : 'contained';
    }

    public static function contentMaxWidthCssValue(?string $width = null): string
    {
        $width ??= self::contentMaxWidth();

        if ($width instanceof Width) {
            $width = $width->value;
        }

        if (! is_string($width) || blank($width)) {
            return 'var(--container-7xl)';
        }

        return self::WIDTH_CSS_VALUES[$width] ?? $width;
    }

    protected static function sanitizePreferences(array $preferences): array
    {
        $defaults = [
            'content_full_width' => false,
            'content_max_width' => '7xl',
            'content_alignment' => 'left',
            'record_list_pages_full_width' => true,
            'record_list_pages_full_width_toggle' => true,
            'record_list_pages_full_width_storage_key' => 'filament-record-list-pages-full-width',
        ];

        $sanitized = [];

        foreach (self::PREFERENCE_KEYS as $key) {
            $value = $preferences[$key] ?? $defaults[$key];

            if (in_array($key, self::BOOLEAN_PREFERENCE_KEYS, true)) {
                $sanitized[$key] = self::booleanValue($value, (bool) $defaults[$key]);

                continue;
            }

            $sanitized[$key] = match ($key) {
                'content_alignment' => in_array($value, ['left', 'center'], true) ? $value : $defaults[$key],
                'content_max_width' => self::contentMaxWidthValue($value, $defaults[$key]),
                'record_list_pages_full_width_storage_key' => self::stringValue($value, $defaults[$key]),
                default => $value,
            };
        }

        return $sanitized;
    }

    protected static function booleanValue(mixed $value, bool $default): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $boolean = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $boolean ?? $default;
    }

    protected static function stringValue(mixed $value, string $default): string
    {
        if ($value instanceof Width) {
            $value = $value->value;
        }

        if (! is_string($value) || blank($value)) {
            return $default;
        }

        return $value;
    }

    protected static function contentMaxWidthValue(mixed $value, string $default): string
    {
        $value = self::stringValue($value, $default);

        return in_array($value, self::CONTENT_MAX_WIDTH_OPTIONS, true)
            ? $value
            : $default;
    }

    protected static function currentUser(): ?User
    {
        try {
            $user = auth()->user();
        } catch (Throwable) {
            return null;
        }

        return $user instanceof User ? $user : null;
    }

    protected static function currentPageClass(): ?string
    {
        $route = request()->route();

        if ($route) {
            $action = $route->getAction('controller') ?? $route->getAction('uses') ?? $route->getActionName();

            if (is_string($action)) {
                $class = str_contains($action, '@')
                    ? strstr($action, '@', true)
                    : $action;

                if (class_exists($class) && is_a($class, FilamentPage::class, true)) {
                    return $class;
                }
            }
        }

        return self::currentLivewireComponentClass();
    }

    protected static function currentLivewireComponentClass(): ?string
    {
        try {
            $component = app('livewire')->current();
        } catch (Throwable) {
            $component = null;
        }

        if (is_object($component)) {
            return $component::class;
        }

        $components = request()->input('components');

        if (! is_array($components)) {
            return null;
        }

        foreach ($components as $componentPayload) {
            if (! is_array($componentPayload) || ! is_string($componentPayload['snapshot'] ?? null)) {
                continue;
            }

            try {
                $snapshot = json_decode($componentPayload['snapshot'], true, 512, JSON_THROW_ON_ERROR);
                $componentName = data_get($snapshot, 'memo.name');
            } catch (Throwable) {
                continue;
            }

            if (! is_string($componentName) || blank($componentName)) {
                continue;
            }

            try {
                $componentClass = app('livewire.factory')->resolveComponentClass($componentName);
            } catch (Throwable) {
                continue;
            }

            if (class_exists($componentClass)) {
                return $componentClass;
            }
        }

        return null;
    }

    protected static function resourcePageHasActiveRelationManager(string $pageClass): bool
    {
        if (! in_array(HasRelationManagers::class, class_uses_recursive($pageClass), true)) {
            return false;
        }

        if (! self::hasActiveRelationManagerQueryParameter()) {
            return false;
        }

        if (! method_exists($pageClass, 'getResource')) {
            return false;
        }

        $resource = $pageClass::getResource();

        if (! is_string($resource) || (! class_exists($resource)) || (! method_exists($resource, 'getRelations'))) {
            return false;
        }

        return count($resource::getRelations()) > 0;
    }

    protected static function hasActiveRelationManagerQueryParameter(): bool
    {
        $request = request();

        return filled($request->query('relation')) || filled($request->query('activeRelationManager'));
    }
}
