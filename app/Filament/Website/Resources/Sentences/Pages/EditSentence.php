<?php

namespace App\Filament\Website\Resources\Sentences\Pages;

use App\Filament\Website\Resources\Sentences\SentenceResource;
use App\Services\Website\SentenceAiContentGenerator;
use App\Services\Website\SentenceContentGenerator;
use App\Support\Website\WebsiteFeatures;
use App\Traits\SitemapTrait;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Throwable;

class EditSentence extends EditRecord
{

    use SitemapTrait;
    protected static string $resource = SentenceResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (WebsiteFeatures::sentenceContentGeneratorEnabled()) {
            $actions[] = Action::make('generateContent')
                ->label(fn (): string => app(SentenceAiContentGenerator::class)->isConfigured()
                    ? 'Wygeneruj z AI'
                    : 'Wygeneruj lokalnie')
                ->icon('heroicon-m-sparkles')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Wygenerować wpis i meta?')
                ->modalDescription('Ta akcja zapisze aktualny formularz, a następnie nadpisze tytuł, zajawkę, treść wpisu, metatitle i metadescription wygenerowanym szkicem. Jeśli skonfigurowano OpenAI, użyje generatora AI; w przeciwnym razie użyje generatora lokalnego.')
                ->modalSubmitActionLabel('Wygeneruj')
                ->action(function () {
                    $this->save(false, false);

                    $this->record->refresh();

                    $aiGenerator = app(SentenceAiContentGenerator::class);
                    $source = 'lokalnie';

                    try {
                        if ($aiGenerator->isConfigured()) {
                            $generated = $aiGenerator->generate($this->record);
                            $source = 'z AI';
                        } else {
                            $generated = app(SentenceContentGenerator::class)->generate($this->record);
                        }
                    } catch (Throwable $exception) {
                        report($exception);

                        $generated = app(SentenceContentGenerator::class)->generate($this->record);

                        Notification::make()
                            ->warning()
                            ->title('Generator AI nie zadziałał')
                            ->body('Użyto generatora lokalnego. Przyczyna: ' . Str::limit($exception->getMessage(), 240))
                            ->send();
                    }

                    $this->record->fill($generated);
                    $this->record->save();

                    $this->refreshFormData(array_keys($generated));
                    $this->generateSitemap();

                    Notification::make()
                        ->success()
                        ->title("Wygenerowano szkic wpisu i meta {$source}")
                        ->body('Przed publikacją sprawdź treść i wprowadź końcowe poprawki.')
                        ->send();
                });
        }

        $actions[] = DeleteAction::make()
            ->after(fn () => $this->generateSitemap());

        return $actions;
    }

    protected function afterSave(): void
    {
        $this->generateSitemap();
    }
}
