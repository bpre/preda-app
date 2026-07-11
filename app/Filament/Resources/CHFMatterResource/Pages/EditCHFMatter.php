<?php

namespace App\Filament\Resources\CHFMatterResource\Pages;

use App\Filament\Resources\CHFMatterResource;
use App\Filament\Resources\MatterResource;
use App\Models\Matter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCHFMatter extends EditRecord
{
    protected static string $resource = CHFMatterResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        $this->record = Matter::find($record);

        $this->dispatch('registerKey', $this->record->gdrive);

    }

    protected function getHeaderActions(): array
    {
        return [

            ActionGroup::make([

                Action::make('Zmień w potencjalną sprawę')
                    ->color('gray')
                    ->icon('heroicon-m-arrow-path')
                    ->action(function (array $data, $record) {
                        Matter::where('id', $record->id)->update(['is_matter' => 0]);

                        Notification::make()
                            ->title('Zmieniono sprawę CHF w potencjalną sprawę')
                            ->body('Ustaw aktualny etap sprawy')
                            ->success()
                            ->send();

                        $record->is_matter = false;

                        redirect(MatterResource::getEditUrlForMatter($record, [
                            'activeRelationManager' => 0,
                        ]));
                    }),

            ]),

            Action::make('Folder sprawy')
                ->color('primary')
                ->hidden(fn (Matter $record) => $record->gdrive == null)
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->url(function (Matter $record) {
                    return $record->gdrive;
                })
                ->extraAttributes([
                    'class' => 'matter-folder-action',
                ])
                ->openUrlInNewTab(true),

            DeleteAction::make()->hidden(fn (Matter $record) => $record->hasAnyRelation()),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
