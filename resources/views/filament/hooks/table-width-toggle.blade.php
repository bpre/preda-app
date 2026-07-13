@php
    use App\Support\FilamentContentLayout;

    $isCurrentTablePage = FilamentContentLayout::isCurrentTablePage();
@endphp

@if ($isCurrentTablePage)
    <span data-filament-table-width-page hidden></span>
@endif

@if ($isCurrentTablePage && FilamentContentLayout::shouldShowRecordListPagesFullWidthToggle())
    <x-filament::icon-button
        class="fi-table-width-toggle-icon"
        color="gray"
        icon="heroicon-o-arrows-pointing-out"
        label="Włącz pełną szerokość tabel"
        size="sm"
        tooltip="Włącz pełną szerokość tabel"
        data-filament-table-width-toggle
        data-filament-table-width-toggle-mode="expand"
        x-on:click="window.filamentTableWidth?.toggle()"
    />

    <x-filament::icon-button
        class="fi-table-width-toggle-icon"
        color="gray"
        icon="heroicon-o-arrows-pointing-in"
        label="Wyłącz pełną szerokość tabel"
        size="sm"
        tooltip="Wyłącz pełną szerokość tabel"
        data-filament-table-width-toggle
        data-filament-table-width-toggle-mode="collapse"
        x-on:click="window.filamentTableWidth?.toggle()"
    />

    <script>
        window.filamentTableWidth?.markTablePage();
    </script>
@endif
