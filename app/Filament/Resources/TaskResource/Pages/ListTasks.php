<?php

namespace App\Filament\Resources\TaskResource\Pages;

use Filament\Actions\CreateAction;
use App\Models\Task;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use App\Notifications\TaskCreated;
use App\Filament\Resources\TaskResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Support\PresetTab;
use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;

class ListTasks extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;


    protected static string $resource = TaskResource::class;

    public function updatedTableFilters(): void
    {
        if ($this->getTable()->hasDeferredFilters()) {
            $this->tableDeferredFilters = $this->tableFilters;
        }

        $this->handleTableFilterUpdates();

        if (filled(data_get($this->tableFilters, 'matter_id.value'))) {
            $this->tableSortColumn = 'done_at_date';
            $this->tableSortDirection = 'desc';
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->successNotification(

                    Notification::make()
                            ->success()->title('Dodano nowe zadanie.')
                )
                ->after(function($record) {

                    if($record->assigned_to !== $record->created_by)
                    {
                        $recipient = User::find($record->assigned_to);
                        $recipient->notify(new TaskCreated($record));
                    }

                }),

        ];
    }

    public function getTabs(): array
    {
        return [
            'Dla mnie' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_private', false)
                    ->where('assigned_to', auth()->user()->id))
                // ->badge(Task::query()->where('is_private', false)->where('assigned_to', auth()->user()->id)->where('is_done', 0)->count())
                ->defaultColumns(['done_at', 'label', 'task_creator.name', 'priority'])
                ->icon('heroicon-o-list-bullet')
                ->favorite()->default(),
            'Własne' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_private', true)->where('created_by', auth()->user()->id))
                // ->badge(Task::query()->where('is_private', true)->where('created_by', auth()->user()->id)->where('is_done', 0)->count())
                ->icon('heroicon-o-list-bullet')
                ->color('danger')
                ->favorite(),
            'Zlecone przeze mnie' => PresetTab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('is_private', false)->where('created_by', auth()->user()->id))
                // ->badge(Task::query()->where('is_private', false)->where('created_by', auth()->user()->id)->where('is_done', 0)->count())
                ->defaultColumns(['done_at', 'label', 'assignee.name', 'priority'])
                ->icon('heroicon-o-list-bullet')
                ->color('success')
                ->favorite(),
        ];
    }

}
