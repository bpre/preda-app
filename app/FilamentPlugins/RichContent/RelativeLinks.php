<?php
// app/FilamentPlugins/RichContent/RelativeLinks.php

namespace App\FilamentPlugins\RichContent;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Tiptap\Core\Extension;

class RelativeLinks implements RichContentPlugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    /** @return array<Extension> */
    public function getTipTapPhpExtensions(): array
    {
        return [];
    }

    /** @return array<string> */
    public function getTipTapJsExtensions(): array
    {
        return [];
    }

    /** @return array<RichEditorTool> */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('link')
                ->icon(Heroicon::Link)
                ->action('link'),
        ];
    }

    /** @return array<Action> */
    public function getEditorActions(): array
    {
        return [
            Action::make('link')
                ->modalHeading('Wstaw / edytuj link')
                ->modalWidth(Width::Medium)

                // Prefill polami z argumentów: url → href, title → title, target → target
                ->fillForm(fn (array $arguments): array => [
                    'href'   => $arguments['url'] ?? null,
                    'title'  => $arguments['title'] ?? null,
                    'target' => $arguments['target'] ?? null,
                ])

                ->form([
                    TextInput::make('href')
                        ->label('Adres')
                        ->placeholder('/sciezka, ./sciezka, ../sciezka lub https://example.com')
                        ->required()
                        ->rule('regex:/^(\\/?|\\.\\/|\\.\\.\\/|#|mailto:|tel:|https?:\\/\\/|\\/\\/).+/i')
                        ->helperText('Obsługiwane: /, ./, ../, #, mailto:, tel:, http(s)'),

                    TextInput::make('title')
                        ->label('Tytuł (opcjonalnie)'),

                    TextInput::make('target')
                        ->label('target (opcjonalnie)')
                        ->placeholder('_self lub _blank'),
                ])

                ->action(function (array $arguments, array $data, RichEditor $component): void {
                    $attrs = array_filter([
                        'href'   => $data['href']   ?? null,
                        'title'  => $data['title']  ?? null,
                        'target' => $data['target'] ?: null,
                    ], fn ($v) => filled($v));

                    $component->runCommands(
                        [EditorCommand::make('setLink', arguments: [$attrs])],
                        editorSelection: $arguments['editorSelection'] ?? null,
                    );
                }),
        ];
    }
}
