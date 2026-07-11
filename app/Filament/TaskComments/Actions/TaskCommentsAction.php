<?php

namespace App\Filament\TaskComments\Actions;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;

class TaskCommentsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'view';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Komentarze')
            ->tableIcon('heroicon-o-chat-bubble-left-right')
            ->iconButton()
            ->defaultColor('gray')
            ->authorize(fn (Task $record): bool => TaskResource::canView($record))
            ->extraAttributes([
                'class' => 'hidden',
            ])
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Zamknij')
            ->modalWidth(Width::SevenExtraLarge)
            ->modalHeading(fn (Task $record): string => 'Zadanie: ' . $record->label)
            ->modalContent(fn (Task $record): View => view('filament.task-comments.modal', [
                'task' => $record,
            ]));
    }
}
