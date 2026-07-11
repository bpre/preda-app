<?php

namespace App\Filament\AdvancedTables;

use Filament\Contracts\Plugin;
use Filament\Panel;

class AdvancedTablesPlugin implements Plugin
{
    protected bool $favoritesBarDefaultView = true;

    protected bool $persistsActiveViewInSession = false;

    protected bool $resourceEnabled = true;

    protected bool $userViewsEnabled = true;

    protected bool $quickSaveMakeFavorite = true;

    protected bool $createUsingPresetView = true;

    protected bool $viewManagerInFavoritesBar = true;

    public static function make(): static
    {
        return new static();
    }

    public function getId(): string
    {
        return 'advanced-tables';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function favoritesBarDefaultView(bool $condition = true): static
    {
        $this->favoritesBarDefaultView = $condition;

        return $this;
    }

    public function persistActiveViewInSession(bool $condition = true): static
    {
        $this->persistsActiveViewInSession = $condition;

        return $this;
    }

    public function resourceEnabled(bool $condition = true): static
    {
        $this->resourceEnabled = $condition;

        return $this;
    }

    public function userViewsEnabled(bool $condition = true): static
    {
        $this->userViewsEnabled = $condition;

        return $this;
    }

    public function quickSaveMakeFavorite(bool $condition = true): static
    {
        $this->quickSaveMakeFavorite = $condition;

        return $this;
    }

    public function createUsingPresetView(bool $condition = true): static
    {
        $this->createUsingPresetView = $condition;

        return $this;
    }

    public function viewManagerInFavoritesBar(bool $condition = true): static
    {
        $this->viewManagerInFavoritesBar = $condition;

        return $this;
    }

    public function hasFavoritesBarDefaultView(): bool
    {
        return $this->favoritesBarDefaultView;
    }

    public function persistsActiveViewInSession(): bool
    {
        return $this->persistsActiveViewInSession;
    }

    public function isResourceEnabled(): bool
    {
        return $this->resourceEnabled;
    }

    public function hasUserViewsEnabled(): bool
    {
        return $this->userViewsEnabled;
    }

    public function shouldQuickSaveMakeFavorite(): bool
    {
        return $this->quickSaveMakeFavorite;
    }

    public function shouldCreateUsingPresetView(): bool
    {
        return $this->createUsingPresetView;
    }

    public function shouldShowViewManagerInFavoritesBar(): bool
    {
        return $this->viewManagerInFavoritesBar;
    }
}
