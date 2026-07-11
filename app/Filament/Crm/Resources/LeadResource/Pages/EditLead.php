<?php

namespace App\Filament\Crm\Resources\LeadResource\Pages;

use App\Filament\Crm\Resources\LeadResource;
use App\Filament\Resources\MatterResource;
use App\Models\Matter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('archive')
                ->label(function (Matter $record) {
                    return $this->record->is_archived ? 'Przywróć z archiwum' : 'Archiwizuj';
                })
                ->color('gray')
                ->icon(function ($record) {
                    return $record->is_archived ? 'heroicon-m-arrow-uturn-up' : 'heroicon-m-archive-box-arrow-down';
                })
                ->action(function (array $data, $record) {
                    $record->is_archived = ! $record->is_archived;
                    Matter::where('id', $record->id)->update(['is_archived' => $record->is_archived]);
                    Notification::make()->title(
                        $record->is_archived ? 'Zarchiwizowano' : 'Przywrócono z archiwum'
                    )->success()->send();
                }),
            Action::make('Zmień w sprawę')
                ->color('gray')
                ->icon('heroicon-m-arrow-path')
                ->action(function (array $data, $record) {
                    Matter::where('id', $record->id)->update(['is_matter' => 1]);
                    $record->is_matter = true;

                    redirect(MatterResource::getEditUrlForMatter($record));
                }),
            DeleteAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
