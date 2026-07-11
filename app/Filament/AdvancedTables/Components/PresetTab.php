<?php

namespace App\Filament\AdvancedTables\Components;

use BackedEnum;
use Closure;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\View\ComponentAttributeBag;

class PresetTab extends Tab
{
    protected string | array | BackedEnum | Closure | null $presetColor = null;

    protected bool | Closure $isDefaultPreset = false;

    protected bool | Closure $isFavoritePreset = false;

    /**
     * @var array<int, string> | Closure | null
     */
    protected array | Closure | null $defaultColumns = null;

    public function color(string | array | BackedEnum | Closure | null $color): static
    {
        $this->presetColor = $color;

        return $this;
    }

    public function default(mixed $state = true): static
    {
        $this->isDefaultPreset = $state;

        return $this;
    }

    /**
     * @param  array<int, string> | Closure | null  $columns
     */
    public function defaultColumns(array | Closure | null $columns): static
    {
        $this->defaultColumns = $columns;

        return $this;
    }

    public function favorite(bool | Closure $condition = true): static
    {
        $this->isFavoritePreset = $condition;

        return $this;
    }

    public function getPresetColor(): string | array | BackedEnum | null
    {
        return $this->evaluate($this->presetColor);
    }

    /**
     * @return array<mixed>
     */
    public function getExtraAttributes(): array
    {
        return (new ComponentAttributeBag(parent::getExtraAttributes()))
            ->merge($this->getPresetColorExtraAttributes(), escape: false)
            ->getAttributes();
    }

    /**
     * @return array<string, string>
     */
    protected function getPresetColorExtraAttributes(): array
    {
        $color = $this->getPresetColor();

        if (blank($color)) {
            return [
                'class' => 'fi-advanced-table-preset-tab fi-color fi-color-primary',
            ];
        }

        if (is_array($color)) {
            return [
                'class' => 'fi-advanced-table-preset-tab fi-color',
                'style' => collect($color)
                    ->map(fn (string $value, string | int $shade): string => "--color-{$shade}: {$value}")
                    ->implode('; '),
            ];
        }

        $color = $color instanceof BackedEnum ? $color->value : $color;

        return [
            'class' => "fi-advanced-table-preset-tab fi-color fi-color-{$color}",
        ];
    }

    public function isDefaultPreset(): bool
    {
        return (bool) $this->evaluate($this->isDefaultPreset);
    }

    public function isFavoritePreset(): bool
    {
        return (bool) $this->evaluate($this->isFavoritePreset);
    }

    /**
     * @return array<int, string>
     */
    public function getDefaultColumns(): array
    {
        return array_values($this->evaluate($this->defaultColumns) ?? []);
    }
}
