<?php

namespace Tests\Unit;

use App\Filament\AdvancedTables\AdvancedTablesPlugin;
use App\Filament\AdvancedTables\Components\PresetTab;
use Tests\TestCase;

class AdvancedTablesPresetTabTest extends TestCase
{
    public function test_it_stores_advanced_table_preset_metadata(): void
    {
        $tab = PresetTab::make()
            ->color('success')
            ->default()
            ->favorite()
            ->defaultColumns(['done_at', 'label', 'priority']);

        $this->assertSame('success', $tab->getPresetColor());
        $this->assertStringContainsString('fi-advanced-table-preset-tab', $tab->getExtraAttributes()['class']);
        $this->assertStringContainsString('fi-color-success', $tab->getExtraAttributes()['class']);
        $this->assertTrue($tab->isDefaultPreset());
        $this->assertTrue($tab->isFavoritePreset());
        $this->assertSame(['done_at', 'label', 'priority'], $tab->getDefaultColumns());
    }

    public function test_it_uses_primary_color_for_tabs_without_custom_color(): void
    {
        $tab = PresetTab::make()
            ->default();

        $this->assertNull($tab->getPresetColor());
        $this->assertStringContainsString('fi-advanced-table-preset-tab', $tab->getExtraAttributes()['class']);
        $this->assertStringContainsString('fi-color-primary', $tab->getExtraAttributes()['class']);
    }

    public function test_plugin_supports_advanced_tables_compatibility_options(): void
    {
        $plugin = AdvancedTablesPlugin::make()
            ->favoritesBarDefaultView(false)
            ->persistActiveViewInSession()
            ->resourceEnabled(false)
            ->userViewsEnabled(false)
            ->quickSaveMakeFavorite(false)
            ->createUsingPresetView(false)
            ->viewManagerInFavoritesBar(false);

        $this->assertFalse($plugin->hasFavoritesBarDefaultView());
        $this->assertTrue($plugin->persistsActiveViewInSession());
        $this->assertFalse($plugin->isResourceEnabled());
        $this->assertFalse($plugin->hasUserViewsEnabled());
        $this->assertFalse($plugin->shouldQuickSaveMakeFavorite());
        $this->assertFalse($plugin->shouldCreateUsingPresetView());
        $this->assertFalse($plugin->shouldShowViewManagerInFavoritesBar());
    }
}
