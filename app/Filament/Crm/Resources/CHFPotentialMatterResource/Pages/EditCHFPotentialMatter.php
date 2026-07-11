<?php

namespace App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages;

use App\Filament\Crm\Resources\CHFPotentialMatterResource;
use App\Filament\Resources\MatterResource;
use App\Models\Matter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCHFPotentialMatter extends EditRecord
{
    protected static string $resource = CHFPotentialMatterResource::class;

    protected function getHeaderActions(): array
    {
        return [

            DeleteAction::make(),

            Action::make('Zmień w sprawę')
                ->color('gray')
                ->icon('heroicon-m-arrow-path')
                ->action(function (array $data, $record) {

                    Matter::where('id', $record->id)->update(['is_matter' => 1, 'start' => now()]);

                    Notification::make()
                        ->title('Zmieniono potencjalną sprawę w sprawę CHF')
                        ->body('Ustaw aktualny etap sprawy')
                        ->success()
                        ->send();

                    $record->is_matter = true;

                    redirect(MatterResource::getEditUrlForMatter($record, [
                        'activeRelationManager' => 0,
                    ]));
                }),

            Action::make('Folder sprawy')
                ->color('info')
                ->hidden(fn (Matter $record) => $record->gdrive == null)
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->url(function (Matter $record) {
                    return $record->gdrive;
                })
                ->extraAttributes([
                    'class' => 'matter-folder-action',
                    'target' => '_blank',
                ]),

        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
