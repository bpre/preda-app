<?php

namespace App\Filament\AdvancedTables\Concerns;

use App\Filament\AdvancedTables\AdvancedTablesPlugin;
use App\Filament\AdvancedTables\Components\PresetTab;
use Filament\Facades\Filament;
use LogicException;

trait InteractsWithAdvancedTablePresetTabs
{
    protected function loadDefaultActiveTab(): void
    {
        if (filled($this->activeTab)) {
            $this->persistAdvancedTableActiveTabInSession();

            return;
        }

        if ($this->shouldPersistAdvancedTableActiveTabInSession()) {
            $activeTab = session()->get($this->getAdvancedTableActiveTabSessionKey());

            if (filled($activeTab) && array_key_exists($activeTab, $this->getCachedTabs())) {
                $this->activeTab = $activeTab;

                return;
            }
        }

        $this->activeTab = $this->getDefaultActiveTab();
        $this->persistAdvancedTableActiveTabInSession();
    }

    public function getDefaultActiveTab(): string | int | null
    {
        foreach ($this->getCachedTabs() as $key => $tab) {
            if ($tab instanceof PresetTab && $tab->isDefaultPreset()) {
                return $key;
            }
        }

        return parent::getDefaultActiveTab();
    }

    public function updatedActiveTab(): void
    {
        $this->persistAdvancedTableActiveTabInSession();

        $this->resetPage();

        $this->cachedDefaultTableColumnState = null;
        $this->tableColumns = $this->loadTableColumnsFromSession();

        $this->applyTableColumnManager();
    }

    public function getDefaultTableColumnState(): array
    {
        $state = parent::getDefaultTableColumnState();
        $tab = $this->getActiveAdvancedTablePresetTab();

        if (! $tab) {
            return $state;
        }

        $defaultColumns = $tab->getDefaultColumns();

        if ($defaultColumns === []) {
            return $state;
        }

        return $this->applyAdvancedTableDefaultColumns($state, $defaultColumns);
    }

    public function getTableColumnsSessionKey(): string
    {
        return $this->appendAdvancedTableActiveTabToSessionKey(parent::getTableColumnsSessionKey());
    }

    public function getHasReorderedTableColumnsSessionKey(): string
    {
        return $this->appendAdvancedTableActiveTabToSessionKey(parent::getHasReorderedTableColumnsSessionKey());
    }

    protected function loadTableColumnsFromSession(): array
    {
        $state = parent::loadTableColumnsFromSession();
        $sanitizedState = $this->sanitizeAdvancedTableColumnState($state);

        return $sanitizedState === [] ? $this->getDefaultTableColumnState() : $sanitizedState;
    }

    protected function getActiveAdvancedTablePresetTab(): ?PresetTab
    {
        $activeTab = filled($this->activeTab) ? $this->activeTab : $this->getDefaultActiveTab();
        $tab = $this->getCachedTabs()[$activeTab] ?? null;

        return $tab instanceof PresetTab ? $tab : null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $state
     * @param  array<int, string>  $defaultColumns
     * @return array<int, array<string, mixed>>
     */
    protected function applyAdvancedTableDefaultColumns(array $state, array $defaultColumns): array
    {
        $visibleColumns = array_flip(array_map('strval', $defaultColumns));

        return collect($state)
            ->map(fn (array $item): array => $this->applyAdvancedTableDefaultColumnsToItem($item, $visibleColumns))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, int>  $visibleColumns
     * @return array<string, mixed>
     */
    protected function applyAdvancedTableDefaultColumnsToItem(array $item, array $visibleColumns): array
    {
        if (($item['type'] ?? null) === 'group' && isset($item['columns']) && is_array($item['columns'])) {
            $item['columns'] = collect($item['columns'])
                ->map(fn (array $column): array => $this->applyAdvancedTableDefaultColumnsToColumn($column, $visibleColumns))
                ->all();

            $item['isToggled'] = collect($item['columns'])->contains(fn (array $column): bool => (bool) ($column['isToggled'] ?? false));

            return $item;
        }

        return $this->applyAdvancedTableDefaultColumnsToColumn($item, $visibleColumns);
    }

    /**
     * @param  array<string, mixed>  $column
     * @param  array<string, int>  $visibleColumns
     * @return array<string, mixed>
     */
    protected function applyAdvancedTableDefaultColumnsToColumn(array $column, array $visibleColumns): array
    {
        if (! (bool) ($column['isToggleable'] ?? false)) {
            $column['isToggled'] = true;

            return $column;
        }

        $column['isToggled'] = array_key_exists((string) ($column['name'] ?? ''), $visibleColumns);
        $column['isToggledHiddenByDefault'] = ! $column['isToggled'];

        return $column;
    }

    protected function appendAdvancedTableActiveTabToSessionKey(string $sessionKey): string
    {
        $activeTab = filled($this->activeTab) ? $this->activeTab : $this->getDefaultActiveTab();

        if (blank($activeTab)) {
            return $sessionKey;
        }

        return $sessionKey . '.tab.' . md5((string) $activeTab);
    }

    protected function getAdvancedTableActiveTabSessionKey(): string
    {
        return 'advanced-tables.' . md5($this::class) . '.active-tab';
    }

    protected function persistAdvancedTableActiveTabInSession(): void
    {
        if (! $this->shouldPersistAdvancedTableActiveTabInSession()) {
            return;
        }

        session()->put($this->getAdvancedTableActiveTabSessionKey(), $this->activeTab);
    }

    protected function shouldPersistAdvancedTableActiveTabInSession(): bool
    {
        return $this->getAdvancedTablesPlugin()?->persistsActiveViewInSession() ?? false;
    }

    /**
     * @param  array<int, mixed>  $state
     * @return array<int, array<string, mixed>>
     */
    protected function sanitizeAdvancedTableColumnState(array $state): array
    {
        return collect($state)
            ->map(fn (mixed $item): ?array => is_array($item) ? $this->sanitizeAdvancedTableColumnStateItem($item) : null)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    protected function sanitizeAdvancedTableColumnStateItem(array $item): ?array
    {
        if (! is_string($item['type'] ?? null) || ! array_key_exists('name', $item)) {
            return null;
        }

        if (($item['type'] ?? null) === 'group' && isset($item['columns'])) {
            if (! is_array($item['columns'])) {
                return null;
            }

            $item['columns'] = $this->sanitizeAdvancedTableColumnState($item['columns']);
        }

        return $item;
    }

    protected function getAdvancedTablesPlugin(): ?AdvancedTablesPlugin
    {
        try {
            $plugin = Filament::getPlugin('advanced-tables');
        } catch (LogicException) {
            return null;
        }

        return $plugin instanceof AdvancedTablesPlugin ? $plugin : null;
    }
}
