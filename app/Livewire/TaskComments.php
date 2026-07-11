<?php

namespace App\Livewire;

use App\Models\Task;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TaskComments extends Component implements HasForms
{
    use InteractsWithForms;

    public Task $task;

    public ?array $data = [];

    public function mount(Task $task): void
    {
        $this->task = $task;
        $this->form->fill([
            'comment' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                RichEditor::make('comment')
                    ->label('Dodaj komentarz')
                    ->required()
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'link'],
                        ['blockquote', 'bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function addComment(): void
    {
        $data = $this->form->getState();

        $this->task->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $data['comment'] ?? '',
        ]);

        $this->data['comment'] = null;
        $this->form->fill([
            'comment' => null,
        ]);
        $this->task->refresh();
        $this->dispatch('refresh-page');

        Notification::make()
            ->success()
            ->title('Dodano komentarz.')
            ->send();
    }

    public function render(): View
    {
        return view('livewire.task-comments', [
            'comments' => $this->task->comments()
                ->with('user')
                ->get(),
        ]);
    }
}
